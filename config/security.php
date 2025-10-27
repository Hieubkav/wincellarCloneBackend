<?php

return [

    'frontend' => [
        'origins' => array_values(array_filter(array_map(
            static fn (string $origin): string => trim($origin),
            explode(',', (string) env('FRONTEND_URLS', 'http://localhost:5173'))
        ))),
    ],

    'rate_limit' => [
        'api_per_minute' => (int) env('API_RATE_LIMIT', 60),
        'api_decay_minutes' => (int) env('API_RATE_LIMIT_DECAY', 1),
    ],

    'pii' => [
        'ip_hash_algo' => env('IP_HASH_ALGO', 'sha256'),
        'ip_hash_salt' => env('IP_HASH_SALT', env('APP_KEY')),
    ],

];
