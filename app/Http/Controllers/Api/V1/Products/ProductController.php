<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\V1\ProductCollection;
use App\Http\Resources\V1\ProductResource;
use App\Http\Responses\ErrorResponse;
use App\Models\Product;
use App\Support\Product\ProductPaginator;
use App\Support\Product\ProductSearchBuilder;
use App\Support\Product\ProductSorts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();

            // Check for invalid range parameters
            $priceMin = $filters['price_min'] ?? null;
            $priceMax = $filters['price_max'] ?? null;
            if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
                return ErrorResponse::badRequest(
                    'Invalid price range',
                    [
                        'price_min' => $priceMin,
                        'price_max' => $priceMax,
                        'constraint' => 'price_min must be less than or equal to price_max'
                    ]
                );
            }

            $alcoholMin = $filters['alcohol_min'] ?? null;
            $alcoholMax = $filters['alcohol_max'] ?? null;
            if ($alcoholMin !== null && $alcoholMax !== null && $alcoholMin > $alcoholMax) {
                return ErrorResponse::badRequest(
                    'Invalid alcohol range',
                    [
                        'alcohol_min' => $alcoholMin,
                        'alcohol_max' => $alcoholMax,
                        'constraint' => 'alcohol_min must be less than or equal to alcohol_max'
                    ]
                );
            }

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

            // Use ProductCollection with Resources
            return new ProductCollection($paginator);
        } catch (\Exception $e) {
            // Log error with correlation ID
            logger()->error('Product index error', [
                'correlation_id' => $request->header('X-Correlation-ID'),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            throw $e; // Will be caught by global exception handler
        }
    }

    public function show(string $slug): JsonResource
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
            ->first();

        if (!$product) {
            throw ApiException::notFound('Product', $slug);
        }

        return new ProductResource($product);
    }
}
