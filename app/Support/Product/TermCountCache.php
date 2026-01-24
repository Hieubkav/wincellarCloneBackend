<?php

namespace App\Support\Product;

use App\Models\ProductType;

/**
 * TermCountCache - In-memory cache for term product counts
 *
 * Purpose: Prevent duplicate counting queries within a single request.
 * When ProductFilterController builds filters, it calls getTermProductCounts()
 * multiple times. This cache ensures we only hit the database once per request.
 *
 * Performance Impact: Reduces N duplicate queries to 1 query per request.
 *
 * Usage:
 * $counts = TermCountCache::getForType($type);
 * TermCountCache::clear(); // Reset between requests (auto-handled by PHP lifecycle)
 */
class TermCountCache
{
    /**
     * In-memory cache storage
     * Key format: "type:{id}" or "all" for no type filter
     *
     * @var array<string, array<int, int>>
     */
    private static array $cache = [];

    /**
     * Get product counts per term, with caching
     *
     * Returns: [term_id => count] mapping
     * Example: [5 => 120, 7 => 45, 12 => 89]
     *
     * @param  ProductType|null  $type  Filter by product type (null = all products)
     * @return array<int, int>
     */
    public static function getForType(?ProductType $type): array
    {
        $cacheKey = $type ? "type:{$type->id}" : 'all';

        // Return cached result if available
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Build and execute query
        $query = \DB::table('product_term_assignments as pta')
            ->join('products', 'products.id', '=', 'pta.product_id')
            ->where('products.active', true);

        if ($type) {
            $query->where('products.type_id', $type->id);
        }

        $counts = $query
            ->selectRaw('pta.term_id, COUNT(DISTINCT pta.product_id) as cnt')
            ->groupBy('pta.term_id')
            ->pluck('cnt', 'term_id')
            ->toArray();

        // Cast to integers (pluck returns strings for numeric values)
        $counts = array_map(fn ($value) => (int) $value, $counts);

        // Store in cache and return
        self::$cache[$cacheKey] = $counts;

        return $counts;
    }

    /**
     * Clear all cached counts
     *
     * Call this when:
     * - Products are created/updated/deleted
     * - Terms are assigned/unassigned
     * - Running tests that need fresh data
     */
    public static function clear(): void
    {
        self::$cache = [];
    }

    /**
     * Clear cache for specific type
     */
    public static function clearForType(?int $typeId): void
    {
        $cacheKey = $typeId ? "type:{$typeId}" : 'all';
        unset(self::$cache[$cacheKey]);
    }

    /**
     * Get cache statistics (for debugging)
     *
     * @return array{keys: array<string>, hit_count: int}
     */
    public static function getStats(): array
    {
        return [
            'keys' => array_keys(self::$cache),
            'hit_count' => count(self::$cache),
        ];
    }
}
