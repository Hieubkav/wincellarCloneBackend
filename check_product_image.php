<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$product = \App\Models\Product::find(126);

echo "Product 126:\n";
echo "Name: {$product->name}\n";
echo "Cover image URL: " . ($product->cover_image_url ?? 'NULL') . "\n";
echo "Main image URL: " . ($product->main_image_url ?? 'NULL') . "\n";
echo "\nImages relation:\n";
foreach ($product->images as $img) {
    echo "  - Image ID {$img->id}: {$img->url}\n";
}
