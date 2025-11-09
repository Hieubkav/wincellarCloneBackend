<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$component = \App\Models\HomeComponent::find(8);

echo "Component 8 config products: " . json_encode($component->config['products']) . "\n";

// Test resolveResources manually
$productIds = [126, 127, 128, 131];

echo "\nTesting product resolve:\n";
foreach ($productIds as $id) {
    $product = \App\Models\Product::find($id);
    if (!$product) {
        echo "  Product {$id}: NOT FOUND\n";
        continue;
    }
    echo "  Product {$id}: exists, active={$product->active}\n";
}

echo "\nWith active() scope:\n";
$activeProducts = \App\Models\Product::active()->whereIn('id', $productIds)->get();
echo "  Found: " . $activeProducts->count() . " active products\n";
foreach ($activeProducts as $p) {
    echo "  - ID {$p->id}: {$p->name}\n";
}

// Test resolveResources with coverImage
echo "\nWith coverImage relation:\n";
$productsWithImage = \App\Models\Product::query()
    ->with(['coverImage'])
    ->active()
    ->whereIn('id', $productIds)
    ->get();
echo "  Found: " . $productsWithImage->count() . " products with coverImage\n";
