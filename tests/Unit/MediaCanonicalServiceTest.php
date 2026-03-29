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
