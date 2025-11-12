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
    public function index(ProductIndexRequest $request)
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
                'categories',
                'type',
            ])
            ->active()
            ->where('slug', $slug)
            ->first();

        if (!$product) {
            throw ApiException::notFound('Product', $slug);
        }

        // Get related products (2 sections)
        $sameTypeProducts = $this->getSameTypeProducts($product, 4);
        $relatedByAttributeProducts = $this->getRelatedByAttributeProducts($product, 4);
        
        $product->setRelation('sameTypeProducts', $sameTypeProducts);
        $product->setRelation('relatedByAttributeProducts', $relatedByAttributeProducts);

        return new ProductResource($product);
    }

    /**
     * Section 1: Get products with same type
     * Only return if >= 4 products, otherwise return empty
     */
    protected function getSameTypeProducts(Product $product, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        if (!$product->type_id) {
            return new \Illuminate\Database\Eloquent\Collection([]);
        }

        $products = Product::query()
            ->with(['coverImage', 'images', 'terms.group', 'categories', 'type'])
            ->active()
            ->where('id', '!=', $product->id)
            ->where('type_id', $product->type_id)
            ->limit($limit)
            ->get();

        // Only return if we have at least 4 products
        return $products->count() >= 4 ? $products : new \Illuminate\Database\Eloquent\Collection([]);
    }

    /**
     * Section 2: Get products with shared attribute terms
     * Find products sharing at least 1 term from catalog_attribute_groups
     * (grape, origin, etc.)
     * Only return if >= 4 products, otherwise return empty
     */
    protected function getRelatedByAttributeProducts(Product $product, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        // Get all terms of this product (excluding brand as it's not for matching)
        $productTerms = $product->terms()
            ->whereHas('group', function ($query) {
                $query->where('code', '!=', 'brand'); // Exclude brand
            })
            ->pluck('catalog_terms.id')
            ->toArray();

        if (empty($productTerms)) {
            return new \Illuminate\Database\Eloquent\Collection([]);
        }

        // Find products that share at least 1 term with this product
        $products = Product::query()
            ->with(['coverImage', 'images', 'terms.group', 'categories', 'type'])
            ->active()
            ->where('id', '!=', $product->id)
            ->whereHas('terms', function ($query) use ($productTerms) {
                $query->whereIn('catalog_terms.id', $productTerms);
            })
            ->limit($limit)
            ->get();

        // Only return if we have at least 4 products
        return $products->count() >= 4 ? $products : new \Illuminate\Database\Eloquent\Collection([]);
    }
}
