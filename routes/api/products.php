<?php

use App\Http\Controllers\Api\V1\Products\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('san-pham')
    ->name('products.')
    ->group(function (): void {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('{slug}', [ProductController::class, 'show'])->name('show');
    });
