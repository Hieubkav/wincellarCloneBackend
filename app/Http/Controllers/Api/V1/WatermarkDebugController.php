<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Setting;
use App\Services\WatermarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Encoders\JpegEncoder;

class WatermarkDebugController extends Controller
{
    public function __construct(
        private WatermarkService $watermarkService
    ) {}

    /**
     * Debug watermark settings and font availability
     * 
     * GET /api/v1/watermark/debug
     */
    public function debug(): JsonResponse
    {
        $setting = Setting::first();

        $fontPaths = [
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/Arial.ttf',
            'C:/Windows/Fonts/segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
            '/usr/share/fonts/TTF/DejaVuSans.ttf',
        ];

        $availableFonts = [];
        foreach ($fontPaths as $path) {
            if (file_exists($path)) {
                $availableFonts[] = $path;
            }
        }

        $customFontPath = storage_path('app/fonts/watermark.ttf');
        $hasCustomFont = file_exists($customFontPath);

        $gdInfo = function_exists('gd_info') ? gd_info() : null;

        return response()->json([
            'watermark_enabled' => $this->watermarkService->isWatermarkEnabled($setting),
            'settings' => $setting ? [
                'type' => $setting->product_watermark_type,
                'text' => $setting->product_watermark_text,
                'text_size' => $setting->product_watermark_text_size,
                'text_position' => $setting->product_watermark_text_position,
                'text_opacity' => $setting->product_watermark_text_opacity,
                'image_position' => $setting->product_watermark_position,
                'image_size' => $setting->product_watermark_size,
                'has_watermark_image' => $setting->productWatermarkImage !== null,
            ] : null,
            'fonts' => [
                'available_system_fonts' => $availableFonts,
                'custom_font_exists' => $hasCustomFont,
                'custom_font_path' => $customFontPath,
                'custom_font_size' => $hasCustomFont ? filesize($customFontPath) : null,
            ],
            'gd_info' => $gdInfo ? [
                'version' => $gdInfo['GD Version'] ?? 'unknown',
                'freetype_support' => $gdInfo['FreeType Support'] ?? false,
                'webp_support' => $gdInfo['WebP Support'] ?? false,
                'jpeg_support' => $gdInfo['JPEG Support'] ?? false,
                'png_support' => $gdInfo['PNG Support'] ?? false,
            ] : null,
            'php_version' => PHP_VERSION,
            'intervention_version' => \Composer\InstalledVersions::getVersion('intervention/image'),
        ]);
    }

    /**
     * Test watermark directly on a sample image
     * 
     * GET /api/v1/watermark/test/{imageId}
     */
    public function test(int $imageId)
    {
        $image = Image::findOrFail($imageId);
        $setting = Setting::first();
        
        $disk = $image->disk ?? 'public';
        $path = $image->file_path;

        if (!Storage::disk($disk)->exists($path)) {
            return response()->json(['error' => 'Image file not found', 'path' => $path], 404);
        }

        $fullPath = Storage::disk($disk)->path($path);
        
        $errors = [];
        $logs = [];
        
        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($fullPath);
            $logs[] = "Image loaded: {$img->width()}x{$img->height()}";
            
            $fontPath = storage_path('app/fonts/watermark.ttf');
            $logs[] = "Font path: {$fontPath}";
            $logs[] = "Font exists: " . (file_exists($fontPath) ? 'yes' : 'no');
            
            if (file_exists($fontPath)) {
                $logs[] = "Font size: " . filesize($fontPath) . " bytes";
            }
            
            $text = $setting->product_watermark_text ?? 'Test Watermark';
            $opacity = ($setting->product_watermark_text_opacity ?? 50) / 100;
            $fontSize = 48;
            
            $x = $img->width() / 2;
            $y = $img->height() - 100;
            
            $logs[] = "Drawing text: '{$text}' at ({$x}, {$y}) with fontSize={$fontSize}, opacity={$opacity}";
            
            // Draw text
            $img->text($text, (int)$x, (int)$y, function (FontFactory $font) use ($fontPath, $fontSize, $opacity) {
                if ($fontPath && file_exists($fontPath)) {
                    $font->filename($fontPath);
                }
                $font->size($fontSize);
                $font->color("rgba(255, 255, 255, {$opacity})");
                $font->align('center');
                $font->valign('middle');
            });
            
            $logs[] = "Text drawn successfully";
            
            // Encode and return
            $encoded = $img->encode(new JpegEncoder(90));
            
            return response($encoded->toString(), 200, [
                'Content-Type' => 'image/jpeg',
                'X-Debug-Logs' => json_encode($logs),
            ]);
            
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
            $errors[] = $e->getTraceAsString();
            
            return response()->json([
                'error' => 'Watermark test failed',
                'message' => $e->getMessage(),
                'logs' => $logs,
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
