<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Models\Product;
use App\Support\Product\ProductFilters;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProductController extends Controller
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
                'show_contact_cta' => $product->should_show_contact_cta,
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

        $grapeTerms = $this->transformTerms($product->termsByGroup('grape'));
        $originTerms = $this->transformTerms($product->termsByGroup('origin'));

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'discount_percent' => $product->discount_percent,
                'show_contact_cta' => $product->should_show_contact_cta,
                'cover_image_url' => $product->cover_image_url,
                'gallery' => $product->gallery_for_output->all(),
                'brand_term' => $this->transformTerm($product->primaryTerm('brand')),
                'country_term' => $this->transformTerm($product->primaryTerm('origin')),
                'grape_terms' => $grapeTerms,
                'origin_terms' => $originTerms,
                'alcohol_percent' => $product->alcohol_percent,
                'volume_ml' => $product->volume_ml,
                'badges' => $product->badges ?? [],
                'category' => $product->productCategory ? [
                    'id' => $product->productCategory->id,
                    'name' => $product->productCategory->name,
                    'slug' => $product->productCategory->slug,
                ] : null,
                'type' => $product->type ? [
                    'id' => $product->type->id,
                    'name' => $product->type->name,
                    'slug' => $product->type->slug,
                ] : null,
                'breadcrumbs' => $this->buildBreadcrumbs($product),
                'meta' => [
                    'title' => $product->meta_title,
                    'description' => $product->meta_description,
                ],
            ],
        ]);
    }

    private function applySorting(Builder $query, ?string $sort): void
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

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\CatalogTerm> $terms
     * @return array<int, array{id:int,name:string,slug:string}>
     */
    private function transformTerms(Collection $terms): array
    {
        return $terms
            ->map(fn ($term) => $this->transformTerm($term))
            ->filter()
            ->values()
            ->all();
    }

    private function buildBreadcrumbs(Product $product): array
    {
        $breadcrumbs = [];

        if ($product->productCategory) {
            $breadcrumbs[] = [
                'label' => $product->productCategory->name,
                'href' => '/san-pham/'.$product->productCategory->slug,
            ];
        }

        if ($product->type) {
            $breadcrumbs[] = [
                'label' => $product->type->name,
                'href' => '/san-pham?type='.$product->type->slug,
            ];
        }

        if ($brand = $product->primaryTerm('brand')) {
            $breadcrumbs[] = [
                'label' => $brand->name,
                'href' => '/san-pham?brand='.$brand->slug,
            ];
        }

        return $breadcrumbs;
    }
}
