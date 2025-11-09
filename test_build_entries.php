<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$component = \App\Models\HomeComponent::find(8);
$config = $component->config;
$itemsConfig = $config['products'] ?? [];

echo "Testing buildProductEntries with real data:\n";
echo "itemsConfig: " . json_encode($itemsConfig) . "\n\n";

// Manually resolve resources like HomeComponentAssembler does
$productIds = [126, 127, 128, 131];
$products = \App\Models\Product::query()
    ->with(['coverImage'])
    ->active()
    ->whereIn('id', $productIds)
    ->get()
    ->keyBy('id');

echo "Resolved products count: " . $products->count() . "\n";

// Create resources bag
$resources = new \App\Services\Api\V1\Home\HomeComponentResources(
    $products,
    collect(),
    collect(),
    collect(),
    function($c, $type, $id) {
        echo "Missing: {$type} {$id}\n";
    }
);

// Call transformer
$transformer = new \App\Services\Api\V1\Home\Transformers\FavouriteProductsTransformer();
$result = $transformer->transform($component, $resources);

if ($result === null) {
    echo "\n❌ Transform returned NULL\n";
} else {
    echo "\n✅ Transform SUCCESS\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
}
