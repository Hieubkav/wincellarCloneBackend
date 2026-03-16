<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'throttle:api'])
    ->prefix('v1')
    ->as('api.v1.')
    ->group(function (): void {
        // Health check endpoint (no auth required)
        Route::get('health', \App\Http\Controllers\Api\V1\HealthController::class)->name('health');

        // Cache management endpoints
        Route::prefix('cache')->as('cache.')->middleware('admin.token')->group(function (): void {
            Route::post('clear', [\App\Http\Controllers\Api\V1\CacheController::class, 'clear'])->name('clear');
            Route::get('status', [\App\Http\Controllers\Api\V1\CacheController::class, 'status'])->name('status');
            Route::get('version', [\App\Http\Controllers\Api\V1\CacheController::class, 'version'])->name('version');
            Route::post('version/increment', [\App\Http\Controllers\Api\V1\CacheController::class, 'incrementVersion'])->name('version.increment');
        });

        // Image proxy endpoint (public, with watermark)
        Route::get('images/{id}', [\App\Http\Controllers\Api\V1\ImageProxyController::class, 'show'])->name('images.show');

        require __DIR__.'/api/home.php';
        require __DIR__.'/api/menus.php';
        require __DIR__.'/api/products.php';
        require __DIR__.'/api/articles.php';
        require __DIR__.'/api/settings.php';
        require __DIR__.'/api/social-links.php';
        require __DIR__.'/api/tracking.php';
        require __DIR__.'/api/admin.php';
    });
