<?php

use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\AdminArticleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AdminUploadController;

Route::prefix('admin')
    ->as('admin.')
    ->group(function (): void {
        // Dashboard & Analytics
        Route::get('dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('dashboard/traffic-chart', [AdminDashboardController::class, 'trafficChart'])->name('dashboard.traffic-chart');
        Route::get('dashboard/top-products', [AdminDashboardController::class, 'topProducts'])->name('dashboard.top-products');
        Route::get('dashboard/top-articles', [AdminDashboardController::class, 'topArticles'])->name('dashboard.top-articles');
        Route::get('dashboard/recent-events', [AdminDashboardController::class, 'recentEvents'])->name('dashboard.recent-events');

        // Products CRUD
        Route::get('products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('products/{id}', [AdminProductController::class, 'show'])->name('products.show');
        Route::post('products', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('products/{id}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('products/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');
        Route::post('products/bulk-delete', [AdminProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');

        // Articles CRUD
        Route::get('articles', [AdminArticleController::class, 'index'])->name('articles.index');
        Route::get('articles/{id}', [AdminArticleController::class, 'show'])->name('articles.show');
        Route::post('articles', [AdminArticleController::class, 'store'])->name('articles.store');
        Route::put('articles/{id}', [AdminArticleController::class, 'update'])->name('articles.update');
        Route::delete('articles/{id}', [AdminArticleController::class, 'destroy'])->name('articles.destroy');
        Route::post('articles/bulk-delete', [AdminArticleController::class, 'bulkDestroy'])->name('articles.bulk-destroy');

        // Upload
        Route::post('upload/image', [AdminUploadController::class, 'uploadImage'])->name('upload.image');
        Route::post('upload/image-url', [AdminUploadController::class, 'uploadImageFromUrl'])->name('upload.image-url');
    });
