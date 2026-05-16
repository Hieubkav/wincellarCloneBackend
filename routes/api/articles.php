<?php

use App\Http\Controllers\Api\V1\Articles\ArticleController;
use Illuminate\Support\Facades\Route;

Route::prefix('bai-viet')
    ->name('articles.')
    ->group(function (): void {
        Route::get('/', [ArticleController::class, 'index'])->name('index');
        Route::get('{slug}', [ArticleController::class, 'show'])->name('show');
    });

Route::get('articles/{categorySlug}/{slug}', [ArticleController::class, 'showInCategory'])
    ->name('articles.show-in-category');

Route::get('article-categories', [ArticleController::class, 'categories'])
    ->name('articles.categories.index');

Route::get('article-categories/{slug}', [ArticleController::class, 'category'])
    ->name('articles.categories.show');

Route::get('noi-dung/{section}/{slug}', [ArticleController::class, 'contentPage'])
    ->name('articles.content-page');
