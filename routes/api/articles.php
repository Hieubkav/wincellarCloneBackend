<?php

use App\Http\Controllers\Api\V1\Articles\ArticleController;
use Illuminate\Support\Facades\Route;

Route::prefix('bai-viet')
    ->name('articles.')
    ->group(function (): void {
        Route::get('/', [ArticleController::class, 'index'])->name('index');
        Route::get('{slug}', [ArticleController::class, 'show'])->name('show');
    });
