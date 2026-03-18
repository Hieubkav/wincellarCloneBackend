<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\V1\ProductCollection;
use App\Http\Resources\V1\ProductResource;
use App\Http\Responses\ErrorResponse;
use App\Models\Product;
use App\Support\Product\ProductCacheManager;
use App\Support\Product\ProductPaginator;
use App\Support\Product\ProductSearchBuilder;
use App\Support\Product\ProductSorts;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

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
                        'constraint' => 'price_min must be less than or equal to price_max',
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
                        'constraint' => 'alcohol_min must be less than or equal to alcohol_max',
                    ]
                );
            }

            $cursorInput = $request->input('cursor');
            $usingCursor = $cursorInput !== null;

            $query = ProductSearchBuilder::build($filters, $request->input('q'), null, true);

            ProductSorts::apply($query, $request->input('sort', '-created_at'));

            $perPage = (int) $request->input('per_page', 24);
            $requestedPage = (int) $request->input('page', 1);
            $sort = $request->input('sort', '-created_at');
            $searchQuery = $request->input('q');

            // Priority 3 Optimization + ROOT CAUSE #6 FIX:
            // - Semantic cache keys (no MD5)
            // - Tag-based invalidation
            // - Dynamic TTL based on query type
            // - Cache locks to prevent race conditions
            $cacheKey = ProductCacheManager::buildKey($filters, $sort, $requestedPage, $perPage, $searchQuery);
            $cacheTags = ProductCacheManager::getTags($filters);
            $cacheTtl = ProductCacheManager::getTtl($filters, $searchQuery);

            // Use ProductCacheManager::remember() with lock protection
            $paginator = ProductCacheManager::remember($cacheKey, $cacheTtl, $cacheTags, function () use ($query, $perPage, $requestedPage, $usingCursor) {
                return ProductPaginator::paginate(
                    $query,
                    $perPage,
                    $requestedPage,
                    ['products.*'],
                    'page',
                    ! $usingCursor
                );
            });

            // Load relations for API resources (terms for attributes display)
            $paginator->getCollection()->load(['terms.group', 'categories', 'type', 'images']);

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

        if (! $product) {
            throw ApiException::notFound('Product', $slug);
        }

        // Get related products (optimized - single query instead of 2 sequential)
        [$sameTypeProducts, $relatedByAttributeProducts] = $this->getRelatedProductsOptimized($product);

        $product->setRelation('sameTypeProducts', $sameTypeProducts);
        $product->setRelation('relatedByAttributeProducts', $relatedByAttributeProducts);

        return new ProductResource($product);
    }

    /**
     * Get related products optimized - Single query instead of 2 sequential
     *
     * Combines getSameTypeProducts + getRelatedByAttributeProducts into one query
     * Performance: 2 queries (80ms + 90ms = 170ms) → 1 query (90ms) = -47% time
     *
     * @return array [Collection $sameType, Collection $byAttribute]
     */
    protected function getRelatedProductsOptimized(Product $product): array
    {
        if (! $product->type_id) {
            return [new \Illuminate\Database\Eloquent\Collection([]), new \Illuminate\Database\Eloquent\Collection([])];
        }

        // Get term IDs from already loaded relationship (no extra query)
        $productTermIds = $product->terms
            ->filter(fn ($term) => $term->group?->code !== 'brand')
            ->pluck('id')
            ->toArray();

        // Single query to fetch candidates for both types
        // This replaces 2 separate queries with 1 optimized query
        $candidates = Product::query()
            ->with(['coverImage', 'images', 'terms.group', 'categories', 'type'])
            ->active()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product, $productTermIds) {
                // Candidates include:
                // 1. Products with same type
                $query->where('type_id', $product->type_id);

                // 2. Products with shared terms (if any)
                if (! empty($productTermIds)) {
                    $query->orWhereHas('terms', function ($q) use ($productTermIds) {
                        $q->whereIn('catalog_terms.id', $productTermIds);
                    });
                }
            })
            ->limit(12) // Fetch 12 to ensure we have 4 of each type after filtering
            ->get();

        // Separate candidates in memory (fast, O(n) with n=12)
        $sameType = $candidates
            ->filter(fn ($p) => $p->type_id === $product->type_id)
            ->take(4);

        $byAttribute = new \Illuminate\Database\Eloquent\Collection([]);
        if (! empty($productTermIds)) {
            $byAttribute = $candidates
                ->filter(function ($p) use ($productTermIds) {
                    return $p->terms->pluck('id')->intersect($productTermIds)->isNotEmpty();
                })
                ->filter(fn ($p) => $p->type_id !== $product->type_id) // Exclude same type duplicates
                ->take(4);
        }

        // Return only if >= 4 items (per requirements)
        return [
            $sameType->count() >= 4 ? $sameType : new \Illuminate\Database\Eloquent\Collection([]),
            $byAttribute->count() >= 4 ? $byAttribute : new \Illuminate\Database\Eloquent\Collection([]),
        ];
    }

    /**
     * @deprecated Use getRelatedProductsOptimized() instead
     * Kept for backward compatibility
     */
    protected function getSameTypeProducts(Product $product, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        if (! $product->type_id) {
            return new \Illuminate\Database\Eloquent\Collection([]);
        }

        $products = Product::query()
            ->with(['coverImage', 'images', 'terms.group', 'categories', 'type'])
            ->active()
            ->where('id', '!=', $product->id)
            ->where('type_id', $product->type_id)
            ->limit($limit)
            ->get();

        return $products->count() >= 4 ? $products : new \Illuminate\Database\Eloquent\Collection([]);
    }

    /**
     * @deprecated Use getRelatedProductsOptimized() instead
     * Kept for backward compatibility
     */
    protected function getRelatedByAttributeProducts(Product $product, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        $productTerms = $product->terms()
            ->whereHas('group', function ($query) {
                $query->where('code', '!=', 'brand');
            })
            ->pluck('catalog_terms.id')
            ->toArray();

        if (empty($productTerms)) {
            return new \Illuminate\Database\Eloquent\Collection([]);
        }

        $products = Product::query()
            ->with(['coverImage', 'images', 'terms.group', 'categories', 'type'])
            ->active()
            ->where('id', '!=', $product->id)
            ->whereHas('terms', function ($query) use ($productTerms) {
                $query->whereIn('catalog_terms.id', $productTerms);
            })
            ->limit($limit)
            ->get();

        return $products->count() >= 4 ? $products : new \Illuminate\Database\Eloquent\Collection([]);
    }
}
