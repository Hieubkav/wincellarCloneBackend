<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Homepage - Landing page with navigation cards
Route::get('/', function () {
    return view('home');
});

// Tổng quan - System overview
Route::get('/tong-quan', function () {
    return view('tong-quan');
})->name('tong-quan');

// Hướng dẫn - User guide with tutorials
Route::get('/huong-dan', function () {
    return view('huong-dan');
})->name('huong-dan');

// Tính năng - Feature list
Route::get('/tinh-nang', function () {
    return view('tinh-nang');
})->name('tinh-nang');

// API Documentation - Full API reference
Route::get('/api-docs', function () {
    return view('api-documentation');
})->name('api-docs');

// Run storage link - for production deployment
Route::get('/run-storage-link', function () {
    Artisan::call('storage:link');
    return response()->json(['message' => 'Storage linked successfully!'], 200);
});

// Serve storage files with aggressive caching
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }
    
    $etag = md5_file($filePath);
    $lastModified = filemtime($filePath);
    
    // Check if client has valid cached version (ETag)
    $clientEtag = request()->header('If-None-Match');
    if ($clientEtag === '"' . $etag . '"') {
        return response('', 304)
            ->header('Cache-Control', 'public, max-age=31536000, immutable')
            ->header('ETag', '"' . $etag . '"')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
    }
    
    // Check if client has valid cached version (Last-Modified)
    $clientLastModified = request()->header('If-Modified-Since');
    if ($clientLastModified && strtotime($clientLastModified) >= $lastModified) {
        return response('', 304)
            ->header('Cache-Control', 'public, max-age=31536000, immutable')
            ->header('ETag', '"' . $etag . '"')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
    }
    
    // Serve file with cache headers
    return response()->file($filePath, [
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'ETag' => '"' . $etag . '"',
        'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
        'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
    ]);
})->where('path', '.*');
