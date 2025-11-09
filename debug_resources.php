<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Component 8: favourite_products with 4 products
$component8 = \App\Models\HomeComponent::find(8);
$productIds = array_column($component8->config['products'] ?? [], 'product_id');
echo "Component 8 (favourite_products) product IDs: " . implode(', ', $productIds) . "\n";

$products = \App\Models\Product::whereIn('id', $productIds)->get(['id', 'active']);
echo "Products found:\n";
foreach ($products as $p) {
    echo "  ID {$p->id}: active=" . ($p->active ? 'Yes' : 'No') . "\n";
}

$activeProducts = \App\Models\Product::active()->whereIn('id', $productIds)->get(['id', 'active']);
echo "Active products: " . $activeProducts->count() . "\n\n";

// Component 9: brand_showcase with 1 brand
$component9 = \App\Models\HomeComponent::find(9);
$brandIds = array_column($component9->config['brands'] ?? [], 'term_id');
echo "Component 9 (brand_showcase) brand IDs: " . implode(', ', $brandIds) . "\n";

$terms = \App\Models\CatalogTerm::whereIn('id', $brandIds)->get(['id', 'active']);
echo "Terms found:\n";
foreach ($terms as $t) {
    echo "  ID {$t->id}: active=" . ($t->active ? 'Yes' : 'No') . "\n";
}

$activeTerms = \App\Models\CatalogTerm::active()->whereIn('id', $brandIds)->get(['id', 'active']);
echo "Active terms: " . $activeTerms->count() . "\n\n";

// Component 13: editorial_spotlight with 1 article
$component13 = \App\Models\HomeComponent::find(13);
$articleIds = array_column($component13->config['articles'] ?? [], 'article_id');
echo "Component 13 (editorial_spotlight) article IDs: " . implode(', ', $articleIds) . "\n";

$articles = \App\Models\Article::whereIn('id', $articleIds)->get(['id', 'active']);
echo "Articles found:\n";
foreach ($articles as $a) {
    echo "  ID {$a->id}: active=" . ($a->active ? 'Yes' : 'No') . "\n";
}

$activeArticles = \App\Models\Article::active()->whereIn('id', $articleIds)->get(['id', 'active']);
echo "Active articles: " . $activeArticles->count() . "\n";
