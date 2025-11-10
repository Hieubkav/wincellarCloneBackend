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
            'categories',
            'type',
        ] : [
            'coverImage',
            'images' => fn ($relation) => $relation->orderBy('order'),
            'terms.group',
            'categories',
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

        $pattern = self::toLikePattern($keyword);

        // Wrap entire search logic in a where closure to maintain proper precedence with other filters
        $query->where(function (Builder $searchQuery) use ($keyword, $pattern): void {
            // MySQL fulltext has minimum word length = 4 (default for InnoDB)
            // Skip MATCH AGAINST for short keywords as they won't match
            if (mb_strlen($keyword) >= 4) {
                // Try full-text search first (better relevance ranking)
                $searchQuery->whereRaw("MATCH(products.name, products.description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$keyword])
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
