<?php

use App\Http\Controllers\Api\ProductsController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('v1')
    ->group(function (): void {
        Route::get('san-pham', [ProductsController::class, 'index']);
    });
