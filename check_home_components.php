<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$components = \App\Models\HomeComponent::query()
    ->orderBy('order')
    ->orderBy('id')
    ->get();

echo "Total components in DB: " . $components->count() . "\n\n";

foreach ($components as $component) {
    echo "ID: {$component->id}\n";
    echo "Type: {$component->type}\n";
    echo "Active: " . ($component->active ? 'Yes' : 'No') . "\n";
    echo "Order: {$component->order}\n";
    
    $config = $component->config;
    
    // Check products
    if (isset($config['products']) && is_array($config['products'])) {
        echo "Products count: " . count($config['products']) . "\n";
    }
    
    // Check articles
    if (isset($config['articles']) && is_array($config['articles'])) {
        echo "Articles count: " . count($config['articles']) . "\n";
    }
    
    // Check brands
    if (isset($config['brands']) && is_array($config['brands'])) {
        echo "Brands count: " . count($config['brands']) . "\n";
    }
    
    // Check slides
    if (isset($config['slides']) && is_array($config['slides'])) {
        echo "Slides count: " . count($config['slides']) . "\n";
    }
    
    // Check banners
    if (isset($config['banners']) && is_array($config['banners'])) {
        echo "Banners count: " . count($config['banners']) . "\n";
    }
    
    // Check categories
    if (isset($config['categories']) && is_array($config['categories'])) {
        echo "Categories count: " . count($config['categories']) . "\n";
    }
    
    echo "---\n\n";
}
