<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Support\Cache\ApiCacheVersionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    /**
     * Clear application cache.
     *
     * This endpoint can be called from frontend to clear cache
     * when data is updated in admin panel.
     */
    public function clear(): JsonResponse
    {
        try {
            ApiCacheVersionManager::bumpFrontendContentVersions();
            $imageVersion = ApiCacheVersionManager::bumpImageProxyVersion();

            return SuccessResponse::make(
                [
                    'api_cache_version' => (int) Cache::get('api_cache_version', 0),
                    'image_proxy_cache_version' => $imageVersion,
                    'last_clear' => Cache::get('last_cache_clear'),
                ],
                'Cache cleared successfully'
            );
        } catch (\Throwable) {
            return ErrorResponse::internalError('Failed to clear cache');
        }
    }

    /**
     * Get cache status and last update time.
     */
    public function status(): JsonResponse
    {
        // Store last cache clear time in cache itself
        $lastClear = Cache::get('last_cache_clear');

        return SuccessResponse::make([
            'last_clear' => $lastClear,
            'cache_driver' => config('cache.default'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Increment cache version for frontend cache busting.
     *
     * Frontend can read this version and bust cache when it changes.
     */
    public function version(): JsonResponse
    {
        $version = (int) Cache::get('api_cache_version', 0);

        return SuccessResponse::make([
            'version' => $version,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Increment cache version.
     *
     * Call this when data is updated to invalidate frontend caches.
     */
    public function incrementVersion(): JsonResponse
    {
        $version = (int) Cache::get('api_cache_version', 0);
        $newVersion = ApiCacheVersionManager::bumpApiVersion();

        return SuccessResponse::make([
            'old_version' => $version,
            'new_version' => $newVersion,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
