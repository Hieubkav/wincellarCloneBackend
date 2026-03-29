<?php

use App\Models\Article;
use App\Models\Image;
use App\Models\Product;
use App\Services\Api\V1\Home\HomeComponentResources;
use Tests\TestCase;

uses(TestCase::class);

test('home component mapImage exposes canonical metadata', function () {
    config(['app.url' => 'https://example.test']);

    $image = new Image([
        'alt' => 'Banner hero',
        'semantic_type' => 'home',
        'file_path' => 'uploads/home/banner.webp',
    ]);
    $image->id = 12;

    $resources = new HomeComponentResources(collect(), collect(), collect(), collect(), fn () => null);

    $payload = $resources->mapImage($image);

    expect($payload)->toMatchArray([
        'id' => 12,
        'canonical_url' => $image->canonical_url,
        'canonical_key' => app(\App\Services\Media\MediaCanonicalService::class)->resolveCanonicalKey($image),
        'semantic_type' => 'home',
    ]);
});

test('home component product summary includes canonical cover url', function () {
    config(['app.url' => 'https://example.test']);

    $cover = new Image([
        'alt' => 'Ảnh sản phẩm',
        'semantic_type' => 'product',
        'file_path' => 'uploads/products/cover.webp',
    ]);
    $cover->id = 21;

    $product = new Product([
        'name' => 'Rượu vang',
        'slug' => 'ruou-vang',
        'price' => 100000,
        'original_price' => 120000,
    ]);
    $product->id = 5;
    $product->setRelation('coverImage', $cover);

    $resources = new HomeComponentResources(collect(), collect(), collect(), collect(), fn () => null);

    $summary = $resources->mapProductSummary($product);

    expect($summary['cover_image_canonical_url'])->toBe($cover->canonical_url);
});

test('home component article summary includes canonical cover url', function () {
    config(['app.url' => 'https://example.test']);

    $cover = new Image([
        'alt' => 'Ảnh bài viết',
        'semantic_type' => 'article',
        'file_path' => 'uploads/articles/cover.webp',
    ]);
    $cover->id = 33;

    $article = new Article([
        'title' => 'Bài viết mới',
        'slug' => 'bai-viet-moi',
        'excerpt' => 'Mô tả ngắn',
    ]);
    $article->id = 9;
    $article->setRelation('coverImage', $cover);

    $resources = new HomeComponentResources(collect(), collect(), collect(), collect(), fn () => null);

    $summary = $resources->mapArticleSummary($article);

    expect($summary['cover_image_canonical_url'])->toBe($cover->canonical_url);
});
