<?php

use App\Http\Controllers\Api\V1\Home\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('home', HomeController::class)->name('home.show');
