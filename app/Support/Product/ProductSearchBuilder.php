<?php

namespace App\Support\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductSearchBuilder
{
    /**
     * Build product query with filters
     *
     * @param  array<string, mixed>  $filters
     * @param  array<int|string, mixed>|null  $withRelations
     * @param  bool  $isList  Whether this is for list view (select fewer columns) or detail view (select all)
     */
    public static function build(array $filters, ?string $keyword, ?array $withRelations = null, bool $isList = true): Builder
    {
        // Select specific columns based on view type to reduce payload size
        // List view: Exclude heavy columns (description, extra_attrs can be large JSON)
        // Detail view: Select all columns
        $columns = $isList
            ? [
                'products.id',
                'products.name',
                'products.slug',
                'products.price',
                'products.original_price',
                // Note: discount_percent is computed property, not a column
                'products.type_id',
                'products.active',
                'products.created_at',
                'products.updated_at',
                // ❌ EXCLUDE heavy columns for list view:
                // - description (TEXT, ~5-10KB per product)
                // - extra_attrs (JSON, ~2-5KB per product)
                // → Saves ~7-15KB per product * 24 products = ~168-360KB per request
            ]
            : ['products.*']; // Detail view needs all columns

        $query = Product::query()
            ->select($columns)
            ->active();

        $relations = $withRelations ?? ($isList ? [
            'images' => fn ($relation) => $relation->orderBy('order'),
            'terms.group',
            'categories',
            'type',
        ] : [
            'images' => fn ($relation) => $relation->orderBy('order'),
            'terms.group',
            'categories',
            'type',
        ]);

        if (! empty($relations)) {
            $query->with($relations);
        }

        $filterPayload = Arr::except($filters, ['q', 'sort', 'per_page', 'page', 'limit', 'cursor']);

        ProductFilters::apply($query, $filterPayload);

        self::applyKeyword($query, $keyword);

        return $query;
    }

    private static function applyKeyword(Builder $query, ?string $keyword): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $pattern = self::toLikePattern($keyword);

        // Wrap entire search logic in a where closure to maintain proper precedence with other filters
        $query->where(function (Builder $searchQuery) use ($keyword, $pattern): void {
            // MySQL fulltext has minimum word length = 4 (default for InnoDB)
            // Skip MATCH AGAINST for short keywords as they won't match
            if (mb_strlen($keyword) >= 4) {
                // Try full-text search first (better relevance ranking)
                $searchQuery->whereRaw('MATCH(products.name, products.description) AGAINST(? IN NATURAL LANGUAGE MODE)', [$keyword])
                    ->orWhere(function (Builder $likeQuery) use ($pattern): void {
                        // Fallback to LIKE search (only name and slug, skip description to avoid false matches)
                        $likeQuery->where('products.name', 'like', $pattern)
                            ->orWhere('products.slug', 'like', $pattern);
                    });
            } else {
                // For short keywords, only use LIKE on name and slug
                $searchQuery->where('products.name', 'like', $pattern)
                    ->orWhere('products.slug', 'like', $pattern);
            }
        });
    }

    private static function toLikePattern(string $value): string
    {
        $value = Str::lower($value);
        $escaped = addcslashes($value, '\\%_');

        return '%'.$escaped.'%';
    }
}
