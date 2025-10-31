<?php

$frontendOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => trim($origin),
    explode(',', (string) env('FRONTEND_URLS', 'http://localhost:3000,http://127.0.0.1:3000,http://localhost:5173,http://127.0.0.1:5173,https://winecellarclone.vercel.app'))
)));

if ($frontendOrigins === []) {
    $frontendOrigins = ['http://localhost:5173'];
}

return [

    'paths' => [
        'api/*',
        'filament/*',
        'admin/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => $frontendOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-Total-Count',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    'max_age' => 86400,

    'supports_credentials' => true,

];
