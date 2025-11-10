<?php

use App\Http\Controllers\Api\V1\Menu\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('menus', MenuController::class)->name('menus.index');
