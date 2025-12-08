<?php

use App\Http\Controllers\Api\V1\Products\ProductController;
use App\Http\Controllers\Api\V1\Products\ProductFilterController;
use App\Http\Controllers\Api\V1\Products\ProductSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('san-pham')
    ->name('products.')
    ->group(function (): void {
        Route::get('filters/options', [ProductFilterController::class, 'index'])->name('filters.options');
        Route::get('search/suggest', [ProductSearchController::class, 'suggest'])->name('search.suggest');
        Route::get('search', [ProductSearchController::class, 'search'])->name('search');
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('{slug}', [ProductController::class, 'show'])->name('show');
    });
