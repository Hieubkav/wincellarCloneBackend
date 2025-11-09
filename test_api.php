<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$components = \App\Models\HomeComponent::query()
    ->active()
    ->orderBy('order')
    ->orderBy('id')
    ->get();

echo "Components from query: " . $components->count() . "\n\n";

$assembler = new \App\Services\Api\V1\Home\HomeComponentAssembler();
$result = $assembler->build($components);

echo "Transformed components: " . count($result) . "\n\n";

foreach ($result as $item) {
    echo "Type: " . $item['type'] . " (ID: " . $item['id'] . ")\n";
}
