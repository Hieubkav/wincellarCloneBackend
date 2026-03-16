<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Setting;
use App\Services\WatermarkService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\GifEncoder;

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

        // Generate cache key based on image + settings (includes setting hash for cache busting)
        $cacheKey = $this->getCacheKey($image, $applyWatermark, $quality);

        // ETag includes settings hash - changes when watermark settings change
        $etag = '"' . md5($cacheKey) . '"';
        
        // Check if browser cache is valid (ETag must match exactly)
        $ifNoneMatch = request()->header('If-None-Match');
        if ($ifNoneMatch === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'no-cache, must-revalidate');
        }

        // Get or cache processed image
        $processed = Cache::remember($cacheKey, now()->addHours(24), function () use ($image, $applyWatermark, $quality) {
            return $this->processImage($image, $applyWatermark, $quality);
        });

        // Use no-cache to force revalidation on each request
        // This ensures ETag check happens and new watermark is served when settings change
        return response($processed['content'], 200, [
            'Content-Type' => $processed['mime'],
            'ETag' => $etag,
            'Cache-Control' => 'no-cache, must-revalidate',
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

        // Encode based on mime type using Intervention Image v3 Encoder objects
        $encoder = $this->getEncoderFromMime($mime, $quality);
        $encoded = $processedImage->encode($encoder);

        return [
            'content' => $encoded->toString(),
            'mime' => $mime,
        ];
    }

    /**
     * Generate cache key for image + settings
     */
    private function getCacheKey(Image $image, bool $applyWatermark, int $quality): string
    {
        $setting = Setting::first();
        $version = $this->getCacheVersion($image->id);
        
        $settingHash = $setting ? md5(json_encode([
            'type' => $setting->product_watermark_type,
            'position' => $setting->product_watermark_position,
            'size' => $setting->product_watermark_size,
            'text' => $setting->product_watermark_text,
            'text_size' => $setting->product_watermark_text_size,
            'text_position' => $setting->product_watermark_text_position,
            'text_opacity' => $setting->product_watermark_text_opacity,
            'text_repeat' => $setting->product_watermark_text_repeat,
            'watermark_id' => $setting->product_watermark_image_id,
            'watermark_updated' => $setting->productWatermarkImage?->updated_at,
        ])) : 'no-setting';

        return sprintf(
            'image_proxy:%d:%s:%s:%s:%d:%d',
            $image->id,
            $image->updated_at->timestamp,
            $applyWatermark ? $settingHash : 'no-wm',
            $version,
            $quality,
            $image->updated_at->timestamp
        );
    }

    /**
     * Get cache version token (global + per-image)
     */
    private function getCacheVersion(int $imageId): string
    {
        $global = (int) Cache::get('image_proxy:cache:version', 1);
        $perImage = (int) Cache::get("image_proxy:cache:version:{$imageId}", 1);

        return 'g'.$global.'.i'.$perImage;
    }

    /**
     * Get Intervention Image v3 Encoder from mime type
     */
    private function getEncoderFromMime(string $mime, int $quality): \Intervention\Image\Interfaces\EncoderInterface
    {
        return match ($mime) {
            'image/png' => new PngEncoder(),
            'image/webp' => new WebpEncoder($quality),
            'image/gif' => new GifEncoder(),
            'image/jpeg', 'image/jpg' => new JpegEncoder($quality),
            default => new JpegEncoder($quality),
        };
    }

    /**
     * Get image format from mime type
     * @deprecated Use getEncoderFromMime() instead for Intervention Image v3
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

        Cache::increment("image_proxy:cache:version:{$image->id}");

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared for image',
            'data' => [
                'image_id' => $image->id,
                'version' => (int) Cache::get("image_proxy:cache:version:{$image->id}", 1),
            ],
        ]);
    }
}
