<?php

use App\Http\Controllers\Api\V1\SettingController;
use Illuminate\Support\Facades\Route;

// GET /api/v1/settings -> api.v1.settings.index (REST convention)
Route::get('settings', SettingController::class)->name('settings.index');
