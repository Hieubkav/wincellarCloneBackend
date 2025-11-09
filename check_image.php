<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$image = \App\Models\Image::find(47);

if (!$image) {
    echo "Image 47: NOT FOUND\n";
    exit;
}

echo "Image 47:\n";
echo "  Active: " . ($image->active ? 'yes' : 'no') . "\n";
echo "  Deleted: " . ($image->deleted_at ? 'yes' : 'no') . "\n";
echo "  URL: " . ($image->url ?? 'null') . "\n";
