<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Models\Product;
use App\Support\Product\ProductOutput;
use App\Support\Product\ProductPaginator;
use App\Support\Product\ProductSearchBuilder;
use App\Support\Product\ProductSorts;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $cursorInput = $request->input('cursor');
        $usingCursor = $cursorInput !== null;

        $query = ProductSearchBuilder::build($filters, $request->input('q'), null, true);

        ProductSorts::apply($query, $request->input('sort', '-created_at'));

        $perPage = (int) $request->input('per_page', 24);
        $requestedPage = (int) $request->input('page', 1);

        // Cache for 5 minutes for non-search, 1 minute for search
        $cacheKey = 'products_' . md5(serialize($filters) . $request->input('q') . $request->input('sort') . $perPage . $requestedPage);
        $cacheTime = empty($request->input('q')) ? 300 : 60; // 5 min non-search, 1 min search

        $paginator = cache()->remember($cacheKey, $cacheTime, function () use ($query, $perPage, $requestedPage, $usingCursor) {
            return ProductPaginator::paginate(
                $query,
                $perPage,
                $requestedPage,
                ['products.*'],
                'page',
                !$usingCursor
            );
        });

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

        $meta = [
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
        ];

        return response()->json([
            'data' => $mapped,
            'meta' => $meta,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with([
                'coverImage',
                'images' => fn ($relation) => $relation->orderBy('order'),
                'terms.group',
                'productCategory',
                'type',
            ])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => ProductOutput::detail($product),
        ]);
    }
}
