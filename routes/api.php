<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('v1')
    ->as('api.v1.')
    ->group(function (): void {
        require __DIR__.'/api/home.php';
        require __DIR__.'/api/products.php';
        require __DIR__.'/api/articles.php';
    });
