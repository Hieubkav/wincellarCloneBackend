<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$component = \App\Models\HomeComponent::find(8);

$assembler = new \App\Services\Api\V1\Home\HomeComponentAssembler();

// Use reflection to test extractIds
$reflection = new ReflectionClass($assembler);
$method = $reflection->getMethod('extractIds');
$method->setAccessible(true);

$config = $component->config;

echo "Config: " . json_encode($config) . "\n\n";

echo "Extracting 'product_id':\n";
$productIds = $method->invoke($assembler, $config, 'product_id');
echo "Found IDs: " . json_encode($productIds) . "\n";
echo "Count: " . count($productIds) . "\n";
