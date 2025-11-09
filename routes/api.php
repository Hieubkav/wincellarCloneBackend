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
        
        require __DIR__.'/api/home.php';
        require __DIR__.'/api/products.php';
        require __DIR__.'/api/articles.php';
    });
