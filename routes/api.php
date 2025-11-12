<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

// Configure rate limiter for API (60 requests per minute per IP)
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)
        ->by($request->ip())
        ->response(function () {
            return response()->json([
                'error' => 'RateLimitExceeded',
                'message' => 'Too many requests. Please slow down.',
                'timestamp' => now()->toIso8601String(),
                'path' => request()->path(),
                'correlation_id' => request()->header('X-Correlation-ID'),
                'details' => ['retry_after' => 60],
            ], 429);
        });
});

Route::middleware(['api', 'throttle:api'])
    ->prefix('v1')
    ->as('api.v1.')
    ->group(function (): void {
        // Health check endpoint (no auth required)
        Route::get('health', \App\Http\Controllers\Api\V1\HealthController::class)->name('health');
        
        // Cache management endpoints
        Route::prefix('cache')->as('cache.')->group(function (): void {
            Route::post('clear', [\App\Http\Controllers\Api\V1\CacheController::class, 'clear'])->name('clear');
            Route::get('status', [\App\Http\Controllers\Api\V1\CacheController::class, 'status'])->name('status');
            Route::get('version', [\App\Http\Controllers\Api\V1\CacheController::class, 'version'])->name('version');
            Route::post('version/increment', [\App\Http\Controllers\Api\V1\CacheController::class, 'incrementVersion'])->name('version.increment');
        });
        
        require __DIR__.'/api/home.php';
        require __DIR__.'/api/menus.php';
        require __DIR__.'/api/products.php';
        require __DIR__.'/api/articles.php';
        require __DIR__.'/api/settings.php';
        require __DIR__.'/api/social-links.php';
        require __DIR__.'/api/tracking.php';
    });
