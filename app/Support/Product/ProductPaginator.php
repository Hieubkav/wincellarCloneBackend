<?php

namespace App\Support\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductPaginator
{
    /**
     * @param  array<int, string>  $columns
     */
    public static function paginate(
        Builder $query,
        int $perPage,
        int $page,
        array $columns = ['products.*'],
        string $pageName = 'page',
        bool $allowPageReset = true
    ): LengthAwarePaginator {
        $paginator = (clone $query)
            ->distinct()
            ->paginate($perPage, $columns, $pageName, $page);

        if (
            $allowPageReset
            && $page > 1
            && $paginator->isEmpty()
            && $paginator->total() > 0
        ) {
            $paginator = (clone $query)
                ->distinct()
                ->paginate($perPage, $columns, $pageName, 1);
        }

        return $paginator;
    }
}
