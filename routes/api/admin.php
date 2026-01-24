<?php

use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\AdminArticleController;
use App\Http\Controllers\Api\V1\Admin\AdminProductTypeController;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogAttributeGroupController;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogTermController;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogBaselineController;
use App\Http\Controllers\Api\V1\Admin\AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\AdminHomeComponentController;
use App\Http\Controllers\Api\V1\Admin\AdminImageController;
use App\Http\Controllers\Api\V1\Admin\AdminSocialLinkController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AdminUploadController;
use App\Http\Controllers\Api\V1\Admin\AdminSettingController;
use App\Http\Controllers\Api\V1\Admin\AdminMenuController;

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
        Route::get('products/list-for-select', [AdminProductController::class, 'listForSelect'])->name('products.list-for-select');
        Route::get('products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('products/{id}', [AdminProductController::class, 'show'])->name('products.show');
        Route::post('products', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('products/{id}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('products/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');
        Route::post('products/bulk-delete', [AdminProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');

        // Articles CRUD
        Route::get('articles/list-for-select', [AdminArticleController::class, 'listForSelect'])->name('articles.list-for-select');
        Route::get('articles', [AdminArticleController::class, 'index'])->name('articles.index');
        Route::get('articles/{id}', [AdminArticleController::class, 'show'])->name('articles.show');
        Route::post('articles', [AdminArticleController::class, 'store'])->name('articles.store');
        Route::put('articles/{id}', [AdminArticleController::class, 'update'])->name('articles.update');
        Route::delete('articles/{id}', [AdminArticleController::class, 'destroy'])->name('articles.destroy');
        Route::post('articles/bulk-delete', [AdminArticleController::class, 'bulkDestroy'])->name('articles.bulk-destroy');

        // Categories CRUD
        Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{id}', [AdminCategoryController::class, 'show'])->name('categories.show');
        Route::post('categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('categories/bulk-delete', [AdminCategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');

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

        // Home Components CRUD
        Route::get('home-components', [AdminHomeComponentController::class, 'index'])->name('home-components.index');
        Route::get('home-components/{id}', [AdminHomeComponentController::class, 'show'])->name('home-components.show');
        Route::post('home-components', [AdminHomeComponentController::class, 'store'])->name('home-components.store');
        Route::put('home-components/{id}', [AdminHomeComponentController::class, 'update'])->name('home-components.update');
        Route::delete('home-components/{id}', [AdminHomeComponentController::class, 'destroy'])->name('home-components.destroy');
        Route::post('home-components/bulk-delete', [AdminHomeComponentController::class, 'bulkDestroy'])->name('home-components.bulk-destroy');
        Route::post('home-components/reorder', [AdminHomeComponentController::class, 'reorder'])->name('home-components.reorder');

        // Images CRUD
        Route::get('images', [AdminImageController::class, 'index'])->name('images.index');
        Route::get('images/{id}', [AdminImageController::class, 'show'])->name('images.show');
        Route::post('images', [AdminImageController::class, 'store'])->name('images.store');
        Route::put('images/{id}', [AdminImageController::class, 'update'])->name('images.update');
        Route::delete('images/{id}', [AdminImageController::class, 'destroy'])->name('images.destroy');
        Route::post('images/bulk-delete', [AdminImageController::class, 'bulkDestroy'])->name('images.bulk-destroy');

        // Social Links CRUD
        Route::get('social-links', [AdminSocialLinkController::class, 'index'])->name('social-links.index');
        Route::get('social-links/{id}', [AdminSocialLinkController::class, 'show'])->name('social-links.show');
        Route::post('social-links', [AdminSocialLinkController::class, 'store'])->name('social-links.store');
        Route::put('social-links/{id}', [AdminSocialLinkController::class, 'update'])->name('social-links.update');
        Route::delete('social-links/{id}', [AdminSocialLinkController::class, 'destroy'])->name('social-links.destroy');
        Route::post('social-links/bulk-delete', [AdminSocialLinkController::class, 'bulkDestroy'])->name('social-links.bulk-destroy');
        Route::post('social-links/reorder', [AdminSocialLinkController::class, 'reorder'])->name('social-links.reorder');

        // Settings (singleton)
        Route::get('settings', [AdminSettingController::class, 'show'])->name('settings.show');
        Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');

        // Menus CRUD
        Route::get('menus', [AdminMenuController::class, 'index'])->name('menus.index');
        Route::get('menus/{id}', [AdminMenuController::class, 'show'])->name('menus.show');
        Route::post('menus', [AdminMenuController::class, 'store'])->name('menus.store');
        Route::put('menus/{id}', [AdminMenuController::class, 'update'])->name('menus.update');
        Route::delete('menus/{id}', [AdminMenuController::class, 'destroy'])->name('menus.destroy');
        Route::post('menus/bulk-delete', [AdminMenuController::class, 'bulkDestroy'])->name('menus.bulk-destroy');
        Route::post('menus/reorder', [AdminMenuController::class, 'reorder'])->name('menus.reorder');

        // Menu Blocks
        Route::post('menus/{menuId}/blocks', [AdminMenuController::class, 'storeBlock'])->name('menus.blocks.store');
        Route::put('menus/{menuId}/blocks/{blockId}', [AdminMenuController::class, 'updateBlock'])->name('menus.blocks.update');
        Route::delete('menus/{menuId}/blocks/{blockId}', [AdminMenuController::class, 'destroyBlock'])->name('menus.blocks.destroy');

        // Menu Block Items
        Route::post('menu-blocks/{blockId}/items', [AdminMenuController::class, 'storeItem'])->name('menu-blocks.items.store');
        Route::put('menu-blocks/{blockId}/items/{itemId}', [AdminMenuController::class, 'updateItem'])->name('menu-blocks.items.update');
        Route::delete('menu-blocks/{blockId}/items/{itemId}', [AdminMenuController::class, 'destroyItem'])->name('menu-blocks.items.destroy');

        // Users CRUD
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{id}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('users/{id}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
