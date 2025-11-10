<?php

namespace App\Support\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ProductFilters
{
    public function __construct(private Builder $query)
    {
    }

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
        $this->applyAlcoholRange($filters);

        return $this->query;
    }

    private function applyTermFilter(string $groupCode, array $termIds): void
    {
        $ids = $this->filterIds($termIds);

        if (empty($ids)) {
            return;
        }

        $this->query->whereHas('terms', function (Builder $query) use ($groupCode, $ids): void {
            $query->whereIn('catalog_terms.id', $ids)
                ->whereHas('group', fn (Builder $groupQuery) => $groupQuery->where('code', $groupCode));
        });
    }

    private function applyCategoryFilter(array $categoryIds): void
    {
        $ids = $this->filterIds($categoryIds);

        if (empty($ids)) {
            return;
        }

        // Use many-to-many relationship via product_category_product table
        $this->query->whereHas('categories', function (Builder $query) use ($ids): void {
            $query->whereIn('product_categories.id', $ids);
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

    private function applyAlcoholRange(array $filters): void
    {
        if (isset($filters['alcohol_min'])) {
            $this->query->whereNotNull('alcohol_percent')
                ->where('alcohol_percent', '>=', (float) $filters['alcohol_min']);
        }

        if (isset($filters['alcohol_max'])) {
            $this->query->whereNotNull('alcohol_percent')
                ->where('alcohol_percent', '<=', (float) $filters['alcohol_max']);
        }
    }

    /**
     * @param array<int|string, mixed> $items
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
