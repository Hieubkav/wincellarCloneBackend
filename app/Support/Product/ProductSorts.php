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
            'price' => ['price', 'asc'],
            '-price' => ['price', 'desc'],
            'name' => ['name', 'asc'],
            '-name' => ['name', 'desc'],
            'created_at' => ['created_at', 'asc'],
            '-created_at' => ['created_at', 'desc'],
        ];

        $sortConfig = Arr::get($mapping, $sortKey, $mapping['-created_at']);

        $query->orderBy($sortConfig[0], $sortConfig[1]);
    }
}
