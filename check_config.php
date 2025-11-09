<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$problematicIds = [8, 9, 11, 12, 13];

foreach ($problematicIds as $id) {
    $component = \App\Models\HomeComponent::find($id);
    
    echo "Component ID {$id} ({$component->type}):\n";
    echo "Config: " . json_encode($component->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "---\n\n";
}
