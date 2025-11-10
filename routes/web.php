<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// Run storage link - for production deployment
Route::get('/run-storage-link', function () {
    Artisan::call('storage:link');
    return response()->json(['message' => 'Storage linked successfully!'], 200);
});
