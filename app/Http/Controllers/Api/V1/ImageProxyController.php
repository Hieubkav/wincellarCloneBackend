<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Setting;
use App\Services\WatermarkService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImageProxyController extends Controller
{
    public function __construct(
        private WatermarkService $watermarkService
    ) {}

    /**
     * Serve image with watermark applied
     * 
     * GET /api/v1/images/{id}
     * Query params:
     * - w: watermark enabled (default: 1)
     * - q: quality (default: 85, range: 60-100)
     */
    public function show(int $id)
    {
        $image = Image::findOrFail($id);
        
        // Check if watermark should be applied
        $applyWatermark = request()->boolean('w', true);
        $quality = min(100, max(60, request()->integer('q', 85)));

        // Generate cache key based on image + settings
        $cacheKey = $this->getCacheKey($image, $applyWatermark, $quality);

        // Check if browser cache is valid (ETag)
        $etag = md5($cacheKey);
        if (request()->header('If-None-Match') === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        // Get or cache processed image
        $processed = Cache::remember($cacheKey, now()->addHours(24), function () use ($image, $applyWatermark, $quality) {
            return $this->processImage($image, $applyWatermark, $quality);
        });

        return response($processed['content'], 200, [
            'Content-Type' => $processed['mime'],
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=86400', // 24 hours
            'Last-Modified' => $image->updated_at->toRfc7231String(),
            'Content-Disposition' => 'inline; filename="' . basename($image->file_path) . '"',
        ]);
    }

    /**
     * Process image with optional watermark
     */
    private function processImage(Image $image, bool $applyWatermark, int $quality): array
    {
        $disk = $image->disk ?? config('filesystems.default');
        $path = $image->file_path;

        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'Image file not found');
        }

        $fullPath = Storage::disk($disk)->path($path);
        $mime = $image->mime ?? 'image/jpeg';

        // If watermark disabled or not enabled in settings
        if (! $applyWatermark || ! $this->watermarkService->isWatermarkEnabled()) {
            return [
                'content' => Storage::disk($disk)->get($path),
                'mime' => $mime,
            ];
        }

        // Apply watermark
        $processedImage = $this->watermarkService->applyWatermark($fullPath);

        // Encode based on mime type
        $format = $this->getFormatFromMime($mime);
        $encoded = $processedImage->encode($format, $quality);

        return [
            'content' => (string) $encoded,
            'mime' => $mime,
        ];
    }

    /**
     * Generate cache key for image + settings
     */
    private function getCacheKey(Image $image, bool $applyWatermark, int $quality): string
    {
        $setting = Setting::first();
        
        $settingHash = $setting ? md5(json_encode([
            'type' => $setting->product_watermark_type,
            'position' => $setting->product_watermark_position,
            'size' => $setting->product_watermark_size,
            'text' => $setting->product_watermark_text,
            'text_size' => $setting->product_watermark_text_size,
            'text_position' => $setting->product_watermark_text_position,
            'text_opacity' => $setting->product_watermark_text_opacity,
            'watermark_id' => $setting->product_watermark_image_id,
            'watermark_updated' => $setting->productWatermarkImage?->updated_at,
        ])) : 'no-setting';

        return sprintf(
            'image_proxy:%d:%s:%s:%d:%d',
            $image->id,
            $image->updated_at->timestamp,
            $applyWatermark ? $settingHash : 'no-wm',
            $quality,
            $image->updated_at->timestamp
        );
    }

    /**
     * Get image format from mime type
     */
    private function getFormatFromMime(string $mime): string
    {
        return match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/jpeg', 'image/jpg' => 'jpeg',
            default => 'jpeg',
        };
    }

    /**
     * Clear cache for specific image (admin utility)
     * 
     * POST /api/v1/images/{id}/clear-cache
     */
    public function clearCache(int $id)
    {
        $image = Image::findOrFail($id);
        
        // Clear all cache variants for this image
        $pattern = "image_proxy:{$image->id}:*";
        Cache::flush(); // Simple approach - could be optimized with Redis SCAN

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared for image',
        ]);
    }
}
