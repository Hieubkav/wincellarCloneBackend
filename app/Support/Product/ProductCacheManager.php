<?php

namespace App\Support\Product;

use Illuminate\Support\Facades\Cache;

/**
 * ProductCacheManager - Optimized cache strategy for product queries
 *
 * Priority 3 optimization: Smarter cache keys with tag-based invalidation
 * ROOT CAUSE #6 FIX: Cache race condition prevention with locks and versioning
 *
 * Features:
 * - Semantic cache keys (no MD5 hashing)
 * - Cache tags for easy invalidation by filter type
 * - Separate TTL for different query types
 * - Cache statistics tracking
 * - Cache locks to prevent race conditions
 * - Cache versioning to invalidate all caches atomically
 *
 * Usage:
 * $result = ProductCacheManager::remember($cacheKey, $ttl, $tags, function() {
 *     return $query->get();
 * });
 */
class ProductCacheManager
{
    /**
     * Cache version key - increment this to invalidate ALL product caches
     */
    private const CACHE_VERSION_KEY = 'products:cache:version';

    /**
     * Remember cached value with lock protection
     * Prevents cache stampede and race conditions
     *
     * @param  string  $key  Cache key
     * @param  int  $ttl  Time to live in seconds
     * @param  array<string>  $tags  Cache tags for invalidation
     * @param  callable  $callback  Callback to get fresh data
     */
    public static function remember(string $key, int $ttl, array $tags, callable $callback): mixed
    {
        // Add version to cache key to support global invalidation
        $version = self::getCacheVersion();
        $versionedKey = "{$key}:v{$version}";
        $taggedCache = Cache::tags($tags);

        $cached = $taggedCache->get($versionedKey);
        if ($cached !== null) {
            return $cached;
        }

        $lock = Cache::lock("lock:{$versionedKey}", 10);

        if ($lock->get()) {
            try {
                $cachedAfterLock = $taggedCache->get($versionedKey);
                if ($cachedAfterLock !== null) {
                    return $cachedAfterLock;
                }

                $value = $callback();
                $taggedCache->put($versionedKey, $value, $ttl);

                return $value;
            } finally {
                $lock->release();
            }
        }

        usleep(150000);

        $cachedAfterWait = $taggedCache->get($versionedKey);
        if ($cachedAfterWait !== null) {
            return $cachedAfterWait;
        }

        return $callback();
    }

    /**
     * Get current cache version
     * Version is incremented when cache needs to be invalidated globally
     */
    public static function getCacheVersion(): int
    {
        return (int) Cache::get(self::CACHE_VERSION_KEY, 1);
    }

    /**
     * Increment cache version to invalidate all caches
     * More efficient than Cache::tags()->flush() for large datasets
     */
    public static function incrementVersion(): void
    {
        Cache::increment(self::CACHE_VERSION_KEY);
    }

    /**
     * Build semantic cache key from filters
     *
     * Format: products:v2:{filters}:{sort}:{page}:{perPage}
     * Example: products:v2:type-1:brand-5-7:price-100k-500k:-created_at:1:24
     *
     * Benefits:
     * - Human readable
     * - Easier debugging
     * - Better cache hit rate for similar filters
     *
     * @param  array<string, mixed>  $filters
     */
    public static function buildKey(array $filters, string $sort = '-created_at', int $page = 1, int $perPage = 24, ?string $searchQuery = null): string
    {
        $parts = ['products', 'v2']; // v2 = after optimization

        // Add filter segments in predictable order
        if (! empty($filters['type'])) {
            $types = is_array($filters['type']) ? $filters['type'] : [$filters['type']];
            sort($types);
            $parts[] = 'type-'.implode('-', $types);
        }

        if (! empty($filters['category'])) {
            $categories = is_array($filters['category']) ? $filters['category'] : [$filters['category']];
            sort($categories);
            $parts[] = 'cat-'.implode('-', $categories);
        }

        // Terms (brand, grape, origin, etc.)
        if (! empty($filters['terms']) && is_array($filters['terms'])) {
            foreach ($filters['terms'] as $groupCode => $termIds) {
                if (! empty($termIds) && is_array($termIds)) {
                    $sorted = $termIds;
                    sort($sorted);
                    $parts[] = $groupCode.'-'.implode('-', $sorted);
                }
            }
        }

        // Price range
        if (isset($filters['price_min']) || isset($filters['price_max'])) {
            $min = $filters['price_min'] ?? 0;
            $max = $filters['price_max'] ?? 'inf';
            $parts[] = 'price-'.$min.'-'.$max;
        }

        // Alcohol range
        if (isset($filters['alcohol_min']) || isset($filters['alcohol_max'])) {
            $min = $filters['alcohol_min'] ?? 0;
            $max = $filters['alcohol_max'] ?? 'inf';
            $parts[] = 'alc-'.$min.'-'.$max;
        }

        // Search query (if present)
        if (! empty($searchQuery)) {
            // Use first 30 chars of search query (hashed for length)
            $parts[] = 'q-'.substr(md5($searchQuery), 0, 8);
        }

        // Add sort, page, perPage
        $parts[] = $sort;
        $parts[] = 'p'.$page;
        $parts[] = 'pp'.$perPage;

        return implode(':', $parts);
    }

    /**
     * Get cache tags for filter-based invalidation
     *
     * Tags allow invalidating all caches related to specific filter types:
     * - Cache::tags('products')->flush() - flush all product caches
     * - Cache::tags('products:type:1')->flush() - flush all caches with type_id=1
     * - Cache::tags('products:brand:5')->flush() - flush all caches with brand_id=5
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, string>
     */
    public static function getTags(array $filters): array
    {
        $tags = ['products']; // Base tag for all product queries

        // Add type tags
        if (! empty($filters['type'])) {
            $types = is_array($filters['type']) ? $filters['type'] : [$filters['type']];
            foreach ($types as $typeId) {
                $tags[] = 'products:type:'.$typeId;
            }
        }

        // Add category tags
        if (! empty($filters['category'])) {
            $categories = is_array($filters['category']) ? $filters['category'] : [$filters['category']];
            foreach ($categories as $categoryId) {
                $tags[] = 'products:category:'.$categoryId;
            }
        }

        // Add term tags (brand, grape, etc.)
        if (! empty($filters['terms']) && is_array($filters['terms'])) {
            foreach ($filters['terms'] as $groupCode => $termIds) {
                if (! empty($termIds) && is_array($termIds)) {
                    foreach ($termIds as $termId) {
                        $tags[] = 'products:'.$groupCode.':'.$termId;
                    }
                }
            }
        }

        return array_unique($tags);
    }

    /**
     * Get TTL (time-to-live) in seconds based on query type
     *
     * Strategy:
     * - Base queries (no filters): 10 minutes (most cacheable)
     * - Filtered queries (no search): 5 minutes (fairly stable)
     * - Search queries: 1 minute (dynamic, less cacheable)
     *
     * @param  array<string, mixed>  $filters
     */
    public static function getTtl(array $filters, ?string $searchQuery = null): int
    {
        // Search queries: short TTL (1 minute)
        if (! empty($searchQuery)) {
            return 60;
        }

        // No filters: longer TTL (10 minutes)
        $hasFilters = ! empty($filters['type'])
            || ! empty($filters['category'])
            || ! empty($filters['terms'])
            || isset($filters['price_min'])
            || isset($filters['price_max'])
            || isset($filters['alcohol_min'])
            || isset($filters['alcohol_max']);

        if (! $hasFilters) {
            return 600; // 10 minutes
        }

        // With filters: medium TTL (5 minutes)
        return 300;
    }

    /**
     * Invalidate all product caches
     * Useful when products are updated in admin
     * Uses cache versioning for atomic invalidation
     */
    public static function flushAll(): void
    {
        // Increment version to invalidate all caches atomically
        self::incrementVersion();

        // Also flush tags as fallback
        Cache::tags('products')->flush();
    }

    /**
     * Invalidate caches for specific type
     */
    public static function flushByType(int $typeId): void
    {
        Cache::tags('products:type:'.$typeId)->flush();
    }

    /**
     * Invalidate caches for specific category
     */
    public static function flushByCategory(int $categoryId): void
    {
        Cache::tags('products:category:'.$categoryId)->flush();
    }

    /**
     * Invalidate caches for specific term (brand, grape, etc.)
     */
    public static function flushByTerm(string $groupCode, int $termId): void
    {
        Cache::tags('products:'.$groupCode.':'.$termId)->flush();
    }

    /**
     * Get cache statistics (for monitoring)
     *
     * @return array{total_keys: int, memory_usage: string}
     */
    public static function getStats(): array
    {
        // This would need Redis commands to get actual stats
        // For now, return placeholder
        return [
            'total_keys' => 0,
            'memory_usage' => '0 MB',
            'hit_rate' => '0%',
        ];
    }
}
