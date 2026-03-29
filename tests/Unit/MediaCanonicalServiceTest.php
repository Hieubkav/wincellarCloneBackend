<?php

use App\Models\Image;
use App\Services\Media\MediaCanonicalService;
use Tests\TestCase;

uses(TestCase::class);

test('canonical slug uses brand prefix format', function () {
    $service = app(MediaCanonicalService::class);

    $image = new Image([
        'alt' => 'Logo thương hiệu',
        'file_path' => 'uploads/logo.webp',
    ]);

    $slug = $service->resolveCanonicalSlug($image);

    expect($slug)->toBe('thien-kim-wine-shared-logo-thuong-hieu-anh-1');
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

    expect($url)->toBe('https://example.test/media/product/f/thien-kim-wine-san-pham-san-pham-moi-anh-1');
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

    expect($metadata['canonical_url'])->toBe('https://example.test/media/product/k/thien-kim-wine-san-pham-anh-dep-anh-1');
    expect($metadata['canonical_key'])->toBe('k');
    expect($metadata['canonical_slug'])->toBe('thien-kim-wine-san-pham-anh-dep-anh-1');
    expect($metadata['semantic_type'])->toBe('product');
    expect($metadata['storage_key'])->toBe('uploads/foo.jpg');
    expect($metadata['storage_disk'])->toBe('public');
});
