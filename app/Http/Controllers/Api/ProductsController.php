<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Models\Product;
use App\Support\Product\ProductFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ProductsController extends Controller
{
    public function index(ProductIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $query = Product::query()
            ->select('products.*')
            ->with([
                'coverImage',
                'images' => fn ($relation) => $relation->orderBy('order'),
                'terms.group',
                'productCategory',
                'type',
            ])
            ->active();

        ProductFilters::apply($query, $filters);

        $this->applySorting($query, $request->input('sort', '-created_at'));

        $perPage = (int) $request->input('per_page', 24);
        $page = (int) $request->input('page', 1);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query
            ->distinct()
            ->paginate($perPage, ['products.*'], 'page', $page);

        $collection = $paginator->getCollection()->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'discount_percent' => $product->discount_percent,
                'main_image_url' => $product->cover_image_url,
                'gallery' => $product->gallery_for_output->all(),
                'brand_term' => $this->transformTerm($product->primaryTerm('brand')),
                'country_term' => $this->transformTerm($product->primaryTerm('origin')),
                'alcohol_percent' => $product->alcohol_percent,
                'volume_ml' => $product->volume_ml,
                'badges' => $product->badges ?? [],
            ];
        });

        $meta = [
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'sort' => $request->input('sort', '-created_at'),
        ];

        return response()->json([
            'data' => $collection,
            'meta' => $meta,
        ]);
    }

    private function applySorting($query, ?string $sort): void
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

    private function transformTerm($term): ?array
    {
        if (!$term) {
            return null;
        }

        return [
            'id' => $term->id,
            'name' => $term->name,
            'slug' => $term->slug,
        ];
    }
}
