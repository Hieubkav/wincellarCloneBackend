<?php

use App\Models\Image;
use App\Services\Media\MediaCanonicalService;
use Tests\TestCase;

uses(TestCase::class);

test('canonical slug appends brand suffix', function () {
    $service = app(MediaCanonicalService::class);

    $image = new Image([
        'alt' => 'Logo thương hiệu',
        'file_path' => 'uploads/logo.webp',
    ]);

    $slug = $service->resolveCanonicalSlug($image);

    expect($slug)->toEndWith('thien-kim-wine');
});

test('canonical url uses semantic type and key', function () {
    config(['app.url' => 'https://example.test']);

    $service = app(MediaCanonicalService::class);

    $image = new Image([
        'alt' => 'San pham moi',
        'semantic_type' => 'product',
    ]);
    $image->id = 15;

    $url = $service->getCanonicalUrl($image);

    expect($url)->toBe('https://example.test/media/product/f/san-pham-moi-thien-kim-wine');
});

test('metadataFor returns canonical metadata and storage key', function () {
    config(['app.url' => 'https://example.test']);

    $service = app(MediaCanonicalService::class);

    $image = new Image([
        'alt' => 'Anh dep',
        'file_path' => 'uploads/foo.jpg',
        'disk' => 'public',
        'semantic_type' => 'product',
    ]);
    $image->id = 20;

    $metadata = $service->metadataFor($image);

    expect($metadata['canonical_url'])->toBe('https://example.test/media/product/k/anh-dep-thien-kim-wine');
    expect($metadata['canonical_key'])->toBe('k');
    expect($metadata['canonical_slug'])->toBe('anh-dep-thien-kim-wine');
    expect($metadata['semantic_type'])->toBe('product');
    expect($metadata['storage_key'])->toBe('uploads/foo.jpg');
    expect($metadata['storage_disk'])->toBe('public');
});
