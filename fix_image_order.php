<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Fix all product images to have first image with order=0
$products = \App\Models\Product::with('images')->get();

$updated = 0;
foreach ($products as $product) {
    if ($product->images->isEmpty()) {
        continue;
    }
    
    // Sort images by current order and re-index from 0
    $images = $product->images->sortBy('order')->values();
    
    foreach ($images as $index => $image) {
        if ($image->order != $index) {
            $image->order = $index;
            $image->save();
            $updated++;
        }
    }
}

echo "Updated {$updated} images to correct order starting from 0\n";

// Also fix article images
$articles = \App\Models\Article::with('images')->get();
$articleUpdated = 0;

foreach ($articles as $article) {
    if ($article->images->isEmpty()) {
        continue;
    }
    
    $images = $article->images->sortBy('order')->values();
    
    foreach ($images as $index => $image) {
        if ($image->order != $index) {
            $image->order = $index;
            $image->save();
            $articleUpdated++;
        }
    }
}

echo "Updated {$articleUpdated} article images to correct order starting from 0\n";
