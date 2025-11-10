<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductSearchRequest;
use App\Http\Requests\ProductSuggestRequest;
use App\Models\Product;
use App\Support\Product\ProductFacetAggregator;
use App\Support\Product\ProductOutput;
use App\Support\Product\ProductPaginator;
use App\Support\Product\ProductSearchBuilder;
use App\Support\Product\ProductSorts;
use Illuminate\Http\JsonResponse;

class ProductSearchController extends Controller
{
    public function search(ProductSearchRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $cursorInput = $request->input('cursor');
        $usingCursor = $cursorInput !== null;

        $query = ProductSearchBuilder::build($filters, $request->input('q'));
        ProductSorts::apply($query, $request->input('sort', '-created_at'));

        $aggregates = (new ProductFacetAggregator($query))->build();

        $perPage = (int) $request->input('per_page', 24);
        $requestedPage = (int) $request->input('page', 1);

        $paginator = ProductPaginator::paginate(
            $query,
            $perPage,
            $requestedPage,
            ['products.*'],
            'page',
            !$usingCursor
        );

        $collection = $paginator->getCollection();
        $mapped = $collection
            ->map(static fn (Product $product) => ProductOutput::listItem($product))
            ->values();

        $currentCursor = max(0, ($paginator->currentPage() - 1) * $paginator->perPage());
        $currentCursor = min(
            $currentCursor,
            max(0, $paginator->total() - $collection->count())
        );
        $nextCursor = $paginator->hasMorePages()
            ? $currentCursor + $collection->count()
            : null;
        $previousCursor = $currentCursor > 0
            ? max(0, $currentCursor - $paginator->perPage())
            : null;

        $nextPage = $paginator->hasMorePages()
            ? min($paginator->lastPage(), $paginator->currentPage() + 1)
            : null;
        $previousPage = $paginator->currentPage() > 1
            ? $paginator->currentPage() - 1
            : null;

        return response()->json([
            'data' => $mapped,
            'meta' => [
                'page' => $paginator->currentPage(),
                'requested_page' => $requestedPage,
                'previous_page' => $previousPage,
                'next_page' => $nextPage,
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
                'total' => $paginator->total(),
                'sort' => $request->input('sort', '-created_at'),
                'query' => $request->input('q'),
                'mode' => 'load_more',
                'cursor' => $currentCursor,
                'requested_cursor' => $cursorInput !== null ? (int) $cursorInput : null,
                'next_cursor' => $nextCursor,
                'previous_cursor' => $previousCursor,
                'aggregates' => $aggregates,
            ],
        ]);
    }

    public function suggest(ProductSuggestRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $query = ProductSearchBuilder::build(
            $filters,
            $request->input('q'),
            [
                'coverImage',
                'categories',
                'type',
                'terms.group',
            ]
        );

        ProductSorts::apply($query, $request->input('sort', 'name'));

        $results = $query
            ->distinct()
            ->limit((int) $request->input('limit', 8))
            ->get()
            ->map(static fn (Product $product) => ProductOutput::suggestion($product));

        return response()->json([
            'data' => $results,
            'meta' => [
                'query' => $request->input('q'),
                'limit' => (int) $request->input('limit', 8),
            ],
        ]);
    }
}
