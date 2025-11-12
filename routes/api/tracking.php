<?php

use App\Http\Controllers\Api\V1\TrackingController;
use Illuminate\Support\Facades\Route;

Route::prefix('track')
    ->as('track.')
    ->controller(TrackingController::class)
    ->group(function (): void {
        // Generate new anonymous ID
        Route::get('generate-id', 'generateId')->name('generate-id');
        
        // Track visitor and session
        Route::post('visitor', 'trackVisitor')->name('visitor');
        
        // Track events (product view, article view, CTA contact)
        Route::post('event', 'trackEvent')->name('event');
    });
