<?php

namespace App\Services;

use App\Models\Image;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

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
        
        // Get correct path using Storage facade
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
            
            // Resize watermark
            $size = $this->parseSize($setting->product_watermark_size ?? '128x128');
            $watermark->scale(width: $size, height: $size);

            // Calculate position
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
     * Apply text watermark
     */
    private function applyTextWatermark(ImageInterface $image, Setting $setting): ImageInterface
    {
        $text = $setting->product_watermark_text;
        
        if (! $text) {
            return $image;
        }

        $fontSize = $this->getFontSize($setting->product_watermark_text_size ?? 'medium', $image);
        $opacity = $setting->product_watermark_text_opacity ?? 50;
        $position = $setting->product_watermark_text_position ?? 'center';

        // Calculate position coordinates
        [$x, $y] = $this->calculateTextPosition($image, $position, $fontSize);

        // Font file path (we'll use system font or fallback)
        $fontPath = $this->getFontPath();

        try {
            // Draw text shadow first
            $image->text($text, $x + 2, $y + 2, function ($font) use ($fontPath, $fontSize, $opacity) {
                if ($fontPath) {
                    $font->file($fontPath);
                }
                $font->size($fontSize);
                $font->color('rgba(0, 0, 0, ' . ($opacity * 0.5 / 100) . ')');
                $font->align('center');
                $font->valign('middle');
            });
            
            // Draw main text
            $image->text($text, $x, $y, function ($font) use ($fontPath, $fontSize, $opacity) {
                if ($fontPath) {
                    $font->file($fontPath);
                }
                $font->size($fontSize);
                $font->color('rgba(255, 255, 255, ' . ($opacity / 100) . ')');
                $font->align('center');
                $font->valign('middle');
            });

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
        $padding = $fontSize;

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
        $baseSize = min($image->width(), $image->height()) * 0.1;

        return (int) match ($textSize) {
            'xxsmall' => $baseSize * 0.3,
            'xsmall' => $baseSize * 0.5,
            'small' => $baseSize * 0.7,
            'medium' => $baseSize * 1.0,
            'large' => $baseSize * 1.3,
            'xlarge' => $baseSize * 1.6,
            'xxlarge' => $baseSize * 2.0,
            default => $baseSize,
        };
    }

    /**
     * Get font file path
     */
    private function getFontPath(): ?string
    {
        // Try common system fonts
        $possiblePaths = [
            'C:/Windows/Fonts/arial.ttf',           // Windows
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',  // Linux
            '/System/Library/Fonts/Helvetica.ttc',  // macOS
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Fallback: no font (will use built-in)
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
