<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$component = \App\Models\HomeComponent::find(8);
$config = $component->config;
$itemsConfig = $config['products'] ?? [];

echo "itemsConfig type: " . gettype($itemsConfig) . "\n";
echo "itemsConfig count: " . count($itemsConfig) . "\n";
echo "itemsConfig content: " . json_encode($itemsConfig) . "\n\n";

foreach ($itemsConfig as $key => $item) {
    echo "Item {$key}:\n";
    echo "  Type: " . gettype($item) . "\n";
    echo "  Value: " . var_export($item, true) . "\n";
    echo "  is_array: " . (is_array($item) ? 'yes' : 'no') . "\n";
    
    if (is_array($item)) {
        echo "  Has product_id key: " . (isset($item['product_id']) ? 'yes' : 'no') . "\n";
    }
    echo "\n";
}
