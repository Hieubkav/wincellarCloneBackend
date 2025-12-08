<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$product = \App\Models\Product::with(['terms.group', 'type'])->findOrFail(171);

echo "=== PRODUCT 171: " . $product->name . " ===\n";
echo "Type: " . $product->type?->name . "\n";
echo "Type ID: " . $product->type_id . "\n\n";

echo "=== TERMS (Catalog Attributes) ===\n";
if ($product->terms->isEmpty()) {
    echo "Không có terms\n";
} else {
    $product->terms->load('group');
    $grouped = $product->terms->groupBy(function ($term) {
        return $term->group?->name ?? 'Unknown';
    });
    
    foreach ($grouped as $groupName => $terms) {
        echo "{$groupName}:\n";
        foreach ($terms as $term) {
            echo "  - {$term->name}\n";
        }
    }
}

echo "\n=== EXTRA_ATTRS (Nhập tay) ===\n";
if (empty($product->extra_attrs)) {
    echo "Không có extra_attrs\n";
} else {
    foreach ($product->extra_attrs as $code => $attr) {
        echo "{$attr['label']}: {$attr['value']}\n";
    }
}

echo "\n=== CATALOG ATTRIBUTE GROUPS OF TYPE " . $product->type_id . " ===\n";
$groups = \App\Models\CatalogAttributeGroup::whereHas('types', function ($q) use ($product) {
    $q->where('type_id', $product->type_id);
})->orderBy('position')->get();

if ($groups->isEmpty()) {
    echo "Không có groups\n";
} else {
    foreach ($groups as $group) {
        echo "- {$group->name} ({$group->code}) - Filter type: {$group->filter_type}\n";
    }
}
