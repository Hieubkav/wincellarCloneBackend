<?php

namespace App\Support\Product;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ProductSorts
{
    /**
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $query
     * @param string|null $sort
     */
    public static function apply(Builder $query, ?string $sort): void
    {
        $sortKey = $sort ?: '-created_at';

        $mapping = [
            'price' => ['products.price', 'asc'],
            '-price' => ['products.price', 'desc'],
            'name' => ['products.name', 'asc'],
            '-name' => ['products.name', 'desc'],
            'created_at' => ['products.created_at', 'asc'],
            '-created_at' => ['products.created_at', 'desc'],
        ];

        $sortConfig = Arr::get($mapping, $sortKey, $mapping['-created_at']);

        $query->orderBy($sortConfig[0], $sortConfig[1]);
    }
}
