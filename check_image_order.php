<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$product = \App\Models\Product::with(['images', 'coverImage'])->find(126);

echo "Product 126 images:\n";
foreach ($product->images as $img) {
    echo "  - Image ID {$img->id}: order={$img->order}, url={$img->url}\n";
}

echo "\nCover Image relation:\n";
if ($product->coverImage) {
    echo "  Found: ID {$product->coverImage->id}, order={$product->coverImage->order}\n";
} else {
    echo "  NULL - No image with order=0\n";
}

echo "\nCover Image URL accessor: " . $product->cover_image_url . "\n";
