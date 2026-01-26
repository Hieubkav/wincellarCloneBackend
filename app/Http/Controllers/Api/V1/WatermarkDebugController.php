<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\WatermarkService;
use Illuminate\Http\JsonResponse;

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
}
