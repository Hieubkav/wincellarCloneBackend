<?php

namespace App\Support\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductSearchBuilder
{
    /**
     * @param array<string, mixed> $filters
     * @param string|null $keyword
     * @param array<int|string, mixed>|null $withRelations
     */
    public static function build(array $filters, ?string $keyword, ?array $withRelations = null, bool $isList = true): Builder
    {
        $query = Product::query()
            ->select('products.*')
            ->active();

        $relations = $withRelations ?? ($isList ? [
            'coverImage',
            'terms.group',
            'productCategory',
            'type',
        ] : [
            'coverImage',
            'images' => fn ($relation) => $relation->orderBy('order'),
            'terms.group',
            'productCategory',
            'type',
        ]);

        if (!empty($relations)) {
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

        // Try full-text search first (if index exists)
        $query->whereRaw("MATCH(products.name, products.description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$keyword])
            ->orWhere(function (Builder $searchQuery) use ($keyword): void {
                // Fallback to LIKE search
                $pattern = self::toLikePattern($keyword);

                $searchQuery->where('products.name', 'like', $pattern)
                    ->orWhere('products.slug', 'like', $pattern)
                    ->orWhere('products.description', 'like', $pattern);
            });
    }

    private static function toLikePattern(string $value): string
    {
        $value = Str::lower($value);
        $escaped = addcslashes($value, '\\%_');

        return '%'.$escaped.'%';
    }
}
