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
    public static function build(array $filters, ?string $keyword, ?array $withRelations = null): Builder
    {
        $query = Product::query()
            ->select('products.*')
            ->active();

        $relations = $withRelations ?? [
            'coverImage',
            'images' => fn ($relation) => $relation->orderBy('order'),
            'terms.group',
            'productCategory',
            'type',
        ];

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

        $tokens = preg_split('/\s+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_map(static fn (string $token) => mb_strtolower($token, 'UTF-8'), $tokens);
        $tokens = array_values(array_filter(array_unique($tokens)));

        $query->where(function (Builder $searchQuery) use ($keyword, $tokens): void {
            $pattern = self::toLikePattern($keyword);

            $searchQuery->where('products.name', 'like', $pattern)
                ->orWhere('products.slug', 'like', $pattern)
                ->orWhere('products.description', 'like', $pattern);

            foreach ($tokens as $token) {
                $tokenPattern = self::toLikePattern($token);

                $searchQuery->orWhere('products.name', 'like', $tokenPattern)
                    ->orWhere('products.slug', 'like', $tokenPattern)
                    ->orWhere('products.description', 'like', $tokenPattern);
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
