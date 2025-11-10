<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
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
            // Clear Laravel caches
            Cache::flush();
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cache status and last update time.
     */
    public function status(): JsonResponse
    {
        // Store last cache clear time in cache itself
        $lastClear = Cache::get('last_cache_clear');
        
        return response()->json([
            'data' => [
                'last_clear' => $lastClear,
                'cache_driver' => config('cache.default'),
                'timestamp' => now()->toIso8601String(),
            ],
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
        
        return response()->json([
            'data' => [
                'version' => $version,
                'timestamp' => now()->toIso8601String(),
            ],
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
        $newVersion = $version + 1;
        
        Cache::put('api_cache_version', $newVersion);
        Cache::put('last_cache_clear', now()->toIso8601String());
        
        return response()->json([
            'success' => true,
            'data' => [
                'old_version' => $version,
                'new_version' => $newVersion,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
