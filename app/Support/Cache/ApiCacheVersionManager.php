<?php

namespace App\Support\Cache;

use App\Support\Product\ProductCacheManager;
use Illuminate\Support\Facades\Cache;

class ApiCacheVersionManager
{
    public static function bumpApiVersion(): int
    {
        $newVersion = (int) Cache::get('api_cache_version', 0) + 1;

        Cache::put('api_cache_version', $newVersion);
        Cache::put('last_cache_clear', now()->toIso8601String());

        return $newVersion;
    }

    public static function bumpImageProxyVersion(?int $imageId = null): int
    {
        $key = $imageId === null
            ? 'image_proxy:cache:version'
            : "image_proxy:cache:version:{$imageId}";

        $newVersion = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $newVersion);

        return $newVersion;
    }

    public static function bumpFrontendContentVersions(): void
    {
        ProductCacheManager::incrementVersion();
        self::bumpApiVersion();
    }
}
