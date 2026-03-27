<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Driver Strategy
    |--------------------------------------------------------------------------
    | Determines which storage strategy to use. Options: 'local', 's3', 'cdn'
    | Production should use 'cdn' for best performance.
    |
    */
    'storage_driver' => env('MEDIA_STORAGE_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Base URLs for Different Environments
    |--------------------------------------------------------------------------
    | These are used by strategies to construct absolute URLs.
    |
    */
    'base_urls' => [
        'local' => env('APP_URL', 'https://thienkimwine.vitrasau.info.vn'),
        's3' => env('AWS_CLOUDFRONT_URL', env('AWS_URL')),
        'cdn' => env('MEDIA_CDN_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    | Relative paths within storage. Used by all strategies.
    |
    */
    'paths' => [
        'images' => 'media/images',
        'documents' => 'media/documents',
        'placeholders' => 'placeholders',
    ],

    /*
    |--------------------------------------------------------------------------
    | Placeholder Configuration
    |--------------------------------------------------------------------------
    | Fallback images for different entity types.
    | Paths are relative to storage root
    |
    */
    'placeholders' => [
        'product' => env('MEDIA_PLACEHOLDER_PRODUCT', 'placeholders/wine-bottle.svg'),
        'article' => env('MEDIA_PLACEHOLDER_ARTICLE', 'placeholders/article.svg'),
        'term' => env('MEDIA_PLACEHOLDER_TERM', 'placeholders/term.svg'),
        'default' => env('MEDIA_PLACEHOLDER_DEFAULT', 'placeholders/no-image.svg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    */
    'cdn' => [
        'enabled' => env('MEDIA_USE_CDN', false),
        'url' => env('MEDIA_CDN_URL'),
        'cache_control' => 'public, max-age=31536000, immutable', // 1 year
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    */
    'optimization' => [
        'enabled' => env('MEDIA_OPTIMIZE_IMAGES', true),
        'quality' => env('MEDIA_IMAGE_QUALITY', 85),
        'formats' => ['webp', 'jpg', 'png'],
    ],
];
