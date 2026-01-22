<?php

use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\AdminArticleController;
use App\Http\Controllers\Api\V1\Admin\AdminProductTypeController;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogAttributeGroupController;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogTermController;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogBaselineController;
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

        // Product Types CRUD
        Route::get('product-types', [AdminProductTypeController::class, 'index'])->name('product-types.index');
        Route::get('product-types/{id}', [AdminProductTypeController::class, 'show'])->name('product-types.show');
        Route::post('product-types', [AdminProductTypeController::class, 'store'])->name('product-types.store');
        Route::put('product-types/{id}', [AdminProductTypeController::class, 'update'])->name('product-types.update');
        Route::delete('product-types/{id}', [AdminProductTypeController::class, 'destroy'])->name('product-types.destroy');
        // Product Types - Attribute Groups Management
        Route::post('product-types/{id}/attribute-groups', [AdminProductTypeController::class, 'attachAttributeGroup'])->name('product-types.attach-attribute-group');
        Route::delete('product-types/{id}/attribute-groups/{groupId}', [AdminProductTypeController::class, 'detachAttributeGroup'])->name('product-types.detach-attribute-group');
        Route::put('product-types/{id}/attribute-groups/sync', [AdminProductTypeController::class, 'syncAttributeGroups'])->name('product-types.sync-attribute-groups');

        // Catalog Attribute Groups CRUD
        Route::get('catalog-attribute-groups', [AdminCatalogAttributeGroupController::class, 'index'])->name('catalog-attribute-groups.index');
        Route::get('catalog-attribute-groups/{id}', [AdminCatalogAttributeGroupController::class, 'show'])->name('catalog-attribute-groups.show');
        Route::post('catalog-attribute-groups', [AdminCatalogAttributeGroupController::class, 'store'])->name('catalog-attribute-groups.store');
        Route::put('catalog-attribute-groups/{id}', [AdminCatalogAttributeGroupController::class, 'update'])->name('catalog-attribute-groups.update');
        Route::delete('catalog-attribute-groups/{id}', [AdminCatalogAttributeGroupController::class, 'destroy'])->name('catalog-attribute-groups.destroy');

        // Catalog Terms CRUD
        Route::get('catalog-terms', [AdminCatalogTermController::class, 'index'])->name('catalog-terms.index');
        Route::get('catalog-terms/{id}', [AdminCatalogTermController::class, 'show'])->name('catalog-terms.show');
        Route::post('catalog-terms', [AdminCatalogTermController::class, 'store'])->name('catalog-terms.store');
        Route::put('catalog-terms/{id}', [AdminCatalogTermController::class, 'update'])->name('catalog-terms.update');
        Route::delete('catalog-terms/{id}', [AdminCatalogTermController::class, 'destroy'])->name('catalog-terms.destroy');
        Route::post('catalog-terms/reorder', [AdminCatalogTermController::class, 'reorder'])->name('catalog-terms.reorder');

        // Catalog Baseline
        Route::post('catalog/baseline/seed', [AdminCatalogBaselineController::class, 'seed'])->name('catalog.baseline.seed');

        // Upload
        Route::post('upload/image', [AdminUploadController::class, 'uploadImage'])->name('upload.image');
        Route::post('upload/image-url', [AdminUploadController::class, 'uploadImageFromUrl'])->name('upload.image-url');
    });
