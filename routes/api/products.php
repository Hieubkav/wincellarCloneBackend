<?php

use App\Http\Controllers\Api\V1\Products\ProductController;
use App\Http\Controllers\Api\V1\Products\ProductFilterController;
use Illuminate\Support\Facades\Route;

Route::prefix('san-pham')
    ->name('products.')
    ->group(function (): void {
        Route::get('filters/options', ProductFilterController::class)->name('filters.options');
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('{slug}', [ProductController::class, 'show'])->name('show');
    });
