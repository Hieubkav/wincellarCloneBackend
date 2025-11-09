<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$transformer = new \App\Services\Api\V1\Home\Transformers\FavouriteProductsTransformer();

// Use reflection to access private method
$reflection = new ReflectionClass($transformer);
$method = $reflection->getMethod('toPositiveInt');
$method->setAccessible(true);

$testCases = ['126', 126, '0', 0, '-1', null, 'abc'];

foreach ($testCases as $value) {
    $result = $method->invoke($transformer, $value);
    echo "toPositiveInt(" . var_export($value, true) . ") = " . var_export($result, true) . "\n";
}
