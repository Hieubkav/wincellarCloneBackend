<?php

namespace App\Services;

use App\Models\Image;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;

class WatermarkService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Apply watermark to image based on settings
     */
    public function applyWatermark(string $imagePath, ?Setting $setting = null): ImageInterface
    {
        $setting = $setting ?? Setting::first();
        
        $image = $this->manager->read($imagePath);

        if (! $setting) {
            return $image;
        }

        $type = $setting->product_watermark_type ?? 'image';

        if ($type === 'image') {
            return $this->applyImageWatermark($image, $setting);
        }

        if ($type === 'text') {
            return $this->applyTextWatermark($image, $setting);
        }

        return $image;
    }

    /**
     * Apply image watermark overlay
     */
    private function applyImageWatermark(ImageInterface $image, Setting $setting): ImageInterface
    {
        $position = $setting->product_watermark_position ?? 'none';
        
        if ($position === 'none' || ! $setting->productWatermarkImage) {
            return $image;
        }

        $watermarkImage = $setting->productWatermarkImage;
        $disk = $watermarkImage->disk ?? 'public';
        
        $watermarkPath = Storage::disk($disk)->path($watermarkImage->file_path);
        
        if (! file_exists($watermarkPath)) {
            \Log::warning('Watermark image not found', [
                'path' => $watermarkPath,
                'disk' => $disk,
                'file_path' => $watermarkImage->file_path,
            ]);
            return $image;
        }

        try {
            $watermark = $this->manager->read($watermarkPath);
            
            $size = $this->parseSize($setting->product_watermark_size ?? '128x128');
            $watermark->scale(width: $size, height: $size);

            $positionString = $this->getPositionString($position);

            // Place watermark with 70% opacity
            $image->place($watermark, $positionString, 16, 16, 70);

        } catch (\Exception $e) {
            \Log::error('Watermark application failed', [
                'error' => $e->getMessage(),
                'watermark_path' => $watermarkPath,
            ]);
        }

        return $image;
    }

    /**
     * Apply text watermark using Intervention Image v3 API
     */
    private function applyTextWatermark(ImageInterface $image, Setting $setting): ImageInterface
    {
        $text = $setting->product_watermark_text;
        
        if (! $text) {
            return $image;
        }

        $fontSize = $this->getFontSize($setting->product_watermark_text_size ?? 'medium', $image);
        $opacity = ($setting->product_watermark_text_opacity ?? 50) / 100;
        $position = $setting->product_watermark_text_position ?? 'center';

        // Calculate position coordinates
        [$x, $y] = $this->calculateTextPosition($image, $position, $fontSize);

        // Font file path
        $fontPath = $this->getFontPath();

        try {
            // Draw text shadow first (for better visibility)
            $shadowOpacity = $opacity * 0.5;
            $image->text($text, (int)($x + 2), (int)($y + 2), function (FontFactory $font) use ($fontPath, $fontSize, $shadowOpacity) {
                if ($fontPath) {
                    $font->filename($fontPath);
                }
                $font->size($fontSize);
                $font->color("rgba(0, 0, 0, {$shadowOpacity})");
                $font->align('center');
                $font->valign('middle');
            });
            
            // Draw main text
            $image->text($text, (int)$x, (int)$y, function (FontFactory $font) use ($fontPath, $fontSize, $opacity) {
                if ($fontPath) {
                    $font->filename($fontPath);
                }
                $font->size($fontSize);
                $font->color("rgba(255, 255, 255, {$opacity})");
                $font->align('center');
                $font->valign('middle');
            });

            \Log::info('Text watermark applied', [
                'text' => $text,
                'fontSize' => $fontSize,
                'opacity' => $opacity,
                'position' => [$x, $y],
                'fontPath' => $fontPath,
            ]);

        } catch (\Exception $e) {
            \Log::error('Text watermark failed', [
                'error' => $e->getMessage(),
                'text' => $text,
                'font_path' => $fontPath,
            ]);
        }

        return $image;
    }

    /**
     * Parse size string to integer
     */
    private function parseSize(string $size): int
    {
        return match ($size) {
            '64x64' => 64,
            '96x96' => 96,
            '128x128' => 128,
            '160x160' => 160,
            '192x192' => 192,
            default => 128,
        };
    }

    /**
     * Get position string for place() method
     */
    private function getPositionString(string $position): string
    {
        return match ($position) {
            'top_left' => 'top-left',
            'top_right' => 'top-right',
            'bottom_left' => 'bottom-left',
            'bottom_right' => 'bottom-right',
            default => 'top-left',
        };
    }

    /**
     * Calculate text watermark position
     */
    private function calculateTextPosition(
        ImageInterface $image,
        string $position,
        int $fontSize
    ): array {
        $imgWidth = $image->width();
        $imgHeight = $image->height();
        $padding = $fontSize * 2;

        return match ($position) {
            'top' => [$imgWidth / 2, $padding],
            'center' => [$imgWidth / 2, $imgHeight / 2],
            'bottom' => [$imgWidth / 2, $imgHeight - $padding],
            default => [$imgWidth / 2, $imgHeight / 2],
        };
    }

    /**
     * Get font size based on text size setting and image dimensions
     */
    private function getFontSize(string $textSize, ImageInterface $image): int
    {
        $baseSize = min($image->width(), $image->height()) * 0.08;

        return (int) match ($textSize) {
            'xxsmall' => max(12, $baseSize * 0.4),
            'xsmall' => max(14, $baseSize * 0.6),
            'small' => max(16, $baseSize * 0.8),
            'medium' => max(20, $baseSize * 1.0),
            'large' => max(24, $baseSize * 1.4),
            'xlarge' => max(32, $baseSize * 1.8),
            'xxlarge' => max(40, $baseSize * 2.4),
            default => max(20, $baseSize),
        };
    }

    /**
     * Get font file path
     */
    private function getFontPath(): ?string
    {
        // Check for custom font in storage first
        $customFont = storage_path('app/fonts/watermark.ttf');
        if (file_exists($customFont)) {
            return $customFont;
        }

        // Try common system fonts
        $possiblePaths = [
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/Arial.ttf',
            'C:/Windows/Fonts/segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Check if watermark is enabled
     */
    public function isWatermarkEnabled(?Setting $setting = null): bool
    {
        $setting = $setting ?? Setting::first();

        if (! $setting) {
            return false;
        }

        $type = $setting->product_watermark_type ?? 'image';

        if ($type === 'image') {
            $position = $setting->product_watermark_position ?? 'none';
            return $position !== 'none' && $setting->productWatermarkImage !== null;
        }

        if ($type === 'text') {
            return ! empty($setting->product_watermark_text);
        }

        return false;
    }
}
