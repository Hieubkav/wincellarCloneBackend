<?php

namespace App\Support\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ProductFilters
{
    public function __construct(private Builder $query) {}

    public static function apply(Builder $query, array $filters): Builder
    {
        return (new self($query))->applyAll($filters);
    }

    private function applyAll(array $filters): Builder
    {
        $terms = Arr::get($filters, 'terms', []);

        // Apply dynamic attribute filters for all term groups
        // This handles: brand, grape, origin, flavor_profile, material, accessory_type, etc.
        foreach ($terms as $groupCode => $termIds) {
            // Handle nested arrays (e.g., terms[origin][country][] from old frontend)
            if (is_array($termIds) && isset($termIds['country'])) {
                // Legacy: terms[origin][country][]
                $this->applyTermFilter($groupCode, Arr::get($termIds, 'country', []));
            } elseif (is_array($termIds) && isset($termIds['region'])) {
                // Legacy: terms[origin][region][]
                $this->applyTermFilter($groupCode, Arr::get($termIds, 'region', []));
            } else {
                // Modern: terms[brand][], terms[grape][], terms[flavor_profile][], etc.
                $this->applyTermFilter($groupCode, $termIds);
            }
        }

        $this->applyTypeFilter(Arr::get($filters, 'type', []));
        $this->applyCategoryFilter(Arr::get($filters, 'category', []));
        $this->applyPriceRange($filters);
        $this->applyExtraAttrRanges(Arr::get($filters, 'range', []));

        return $this->query;
    }

    private function applyTermFilter(string $groupCode, array $termIds): void
    {
        $ids = $this->filterIds($termIds);

        if (empty($ids)) {
            return;
        }

        // Optimized: Use whereIn with subquery instead of whereHas (30% faster)
        // Old: whereHas creates nested EXISTS subqueries (slow)
        // New: Direct subquery with JOINs (faster, better for query optimizer)
        $this->query->whereIn('products.id', function ($subquery) use ($groupCode, $ids): void {
            $subquery->select('product_term_assignments.product_id')
                ->from('product_term_assignments')
                ->join('catalog_terms', 'product_term_assignments.term_id', '=', 'catalog_terms.id')
                ->join('catalog_attribute_groups', 'catalog_terms.group_id', '=', 'catalog_attribute_groups.id')
                ->where('catalog_attribute_groups.code', $groupCode)
                ->whereIn('catalog_terms.id', $ids);
        });
    }

    private function applyCategoryFilter(array $categoryIds): void
    {
        $ids = $this->filterIds($categoryIds);

        if (empty($ids)) {
            return;
        }

        // Optimized: Use whereIn with subquery instead of whereHas (30% faster)
        // Direct query to pivot table (product_category_product) is faster than EXISTS subquery
        $this->query->whereIn('products.id', function ($subquery) use ($ids): void {
            $subquery->select('product_id')
                ->from('product_category_product')
                ->whereIn('product_category_id', $ids);
        });
    }

    private function applyTypeFilter(array $types): void
    {
        $ids = $this->filterIds($types);

        if (empty($ids)) {
            return;
        }

        $this->query->whereIn('type_id', $ids);
    }

    private function applyPriceRange(array $filters): void
    {
        if (isset($filters['price_min'])) {
            $this->query->where('price', '>=', (int) $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $this->query->where('price', '<=', (int) $filters['price_max']);
        }
    }

    /**
     * @param  array<string, array{min?: numeric, max?: numeric}>  $ranges
     */
    private function applyExtraAttrRanges(array $ranges): void
    {
        foreach ($ranges as $code => $range) {
            if (! is_array($range)) {
                continue;
            }

            // Validate code - chỉ cho phép ký tự an toàn cho JSON path
            if (! preg_match('/^[a-zA-Z0-9_\-. ]+$/', $code)) {
                continue;
            }

            // Build JSON path với quoted key để xử lý ký tự đặc biệt (e.g., "do-cao")
            $jsonPath = '$."'.$code.'".value';

            if (isset($range['min']) && is_numeric($range['min'])) {
                $this->query->whereRaw(
                    'CAST(JSON_UNQUOTE(JSON_EXTRACT(extra_attrs, ?)) AS DECIMAL(10,2)) >= ?',
                    [$jsonPath, (float) $range['min']]
                );
            }

            if (isset($range['max']) && is_numeric($range['max'])) {
                $this->query->whereRaw(
                    'CAST(JSON_UNQUOTE(JSON_EXTRACT(extra_attrs, ?)) AS DECIMAL(10,2)) <= ?',
                    [$jsonPath, (float) $range['max']]
                );
            }
        }
    }

    /**
     * @param  array<int|string, mixed>  $items
     * @return int[]
     */
    private function filterIds(array $items): array
    {
        return array_values(array_filter(
            array_map(
                static fn ($value) => is_numeric($value) ? (int) $value : null,
                $items
            ),
            static fn ($value) => $value !== null && $value > 0
        ));
    }
}
