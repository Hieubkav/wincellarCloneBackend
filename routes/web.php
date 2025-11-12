<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('api-documentation');
});

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
