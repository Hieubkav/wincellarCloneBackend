<?php

use App\Http\Controllers\Api\V1\SocialLink\SocialLinkController;
use Illuminate\Support\Facades\Route;

/**
 * Social Links API Routes
 * 
 * Public endpoints for fetching active social media links
 * Used in: Footer, Contact page
 */

Route::prefix('social-links')
    ->as('social-links.')
    ->group(function (): void {
        // GET /api/v1/social-links - List active social links
        Route::get('/', [SocialLinkController::class, 'index'])->name('index');
    });
