<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$components = \App\Models\HomeComponent::query()
    ->whereIn('id', [8, 9, 11, 12, 13])
    ->orderBy('id')
    ->get();

$assembler = new \App\Services\Api\V1\Home\HomeComponentAssembler();

foreach ($components as $component) {
    echo "\n=== Component {$component->id} ({$component->type}) ===\n";
    echo "Config: " . json_encode($component->config) . "\n";
    
    try {
        $result = $assembler->build(collect([$component]));
        
        if (empty($result)) {
            echo "❌ Transformer returned NULL or empty\n";
        } else {
            echo "✅ Transformer SUCCESS\n";
            echo "Result: " . json_encode($result[0], JSON_PRETTY_PRINT) . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
}
