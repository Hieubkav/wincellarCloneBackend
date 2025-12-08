<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,

            // Price information
            'price' => $this->price,
            'original_price' => $this->original_price,
            'discount_percent' => $this->discount_percent,
            'show_contact_cta' => $this->should_show_contact_cta,

            // Images
            'main_image_url' => $this->cover_image_url ?: '/placeholder/wine-bottle.svg',
            'gallery' => $this->gallery_for_output,

            // Terms/Taxonomy
            'brand_term' => $this->when($this->relationLoaded('terms'), function () {
                $brand = $this->primaryTerm('brand');

                return $brand ? [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ] : null;
            }),

            'country_term' => $this->when($this->relationLoaded('terms'), function () {
                $country = $this->primaryTerm('origin');

                return $country ? [
                    'id' => $country->id,
                    'name' => $country->name,
                    'slug' => $country->slug,
                ] : null;
            }),

            // Conditional fields for detail view
            'description' => $this->when($request->routeIs('api.v1.products.show'), $this->description),

            'grape_terms' => $this->when($request->routeIs('api.v1.products.show') && $this->relationLoaded('terms'), function () {
                return $this->termsByGroup('grape')->map(fn ($term) => [
                    'id' => $term->id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ])->values();
            }),

            'origin_terms' => $this->when($request->routeIs('api.v1.products.show') && $this->relationLoaded('terms'), function () {
                return $this->termsByGroup('origin')->map(fn ($term) => [
                    'id' => $term->id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ])->values();
            }),

            // Product attributes
            'volume_ml' => $this->volume_ml,
            'badges' => $this->badges ?? [],

            // Extra attributes (nhập tay từ admin: dung tích custom, độ cồn custom, v.v.)
            'extra_attrs' => $this->transformExtraAttrs(),

            // All attributes grouped by catalog_attribute_group (for both list and detail view)
            'attributes' => $this->when($this->relationLoaded('terms'), function () {
                return $this->terms
                    ->groupBy(fn ($term) => $term->group?->code ?? 'other')
                    ->map(function ($terms, $groupCode) {
                        $group = $terms->first()?->group;

                        return [
                            'group_code' => $groupCode,
                            'group_name' => $group?->name,
                            'icon_url' => $group?->icon_path
                                ? Storage::disk('public')->url($group->icon_path)
                                : null,
                            'terms' => $terms->map(fn ($t) => [
                                'id' => $t->id,
                                'name' => $t->name,
                                'slug' => $t->slug,
                            ])->values(),
                        ];
                    })
                    ->values();
            }),

            // Categories (now many-to-many)
            'categories' => $this->when($this->relationLoaded('categories'), function () {
                return $this->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])->values();
            }),

            'type' => $this->when($this->relationLoaded('type') && $this->type, [
                'id' => $this->type->id,
                'name' => $this->type->name,
                'slug' => $this->type->slug,
            ]),

            // Breadcrumbs (detail view only)
            'breadcrumbs' => $this->when($request->routeIs('api.v1.products.show'), function () {
                return $this->buildBreadcrumbs();
            }),

            // SEO meta (detail view only)
            'meta' => $this->when($request->routeIs('api.v1.products.show'), [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
            ]),

            // Section 1: Same type products (detail view only)
            'same_type_products' => $this->when(
                $request->routeIs('api.v1.products.show') && $this->relationLoaded('sameTypeProducts'),
                function () {
                    $products = $this->sameTypeProducts;
                    if ($products->isEmpty()) {
                        return null;
                    }

                    return [
                        'products' => $products->map(fn ($product) => $this->mapProductSummary($product))->values(),
                        'view_all_url' => $this->type ? '/filter?type='.$this->type->id : null,
                    ];
                }
            ),

            // Section 2: Related by attributes (detail view only)
            'related_by_attributes' => $this->when(
                $request->routeIs('api.v1.products.show') && $this->relationLoaded('relatedByAttributeProducts'),
                function () {
                    $products = $this->relatedByAttributeProducts;
                    if ($products->isEmpty()) {
                        return null;
                    }

                    // Get first shared term for view_all_url
                    $firstSharedTerm = $this->getFirstSharedTerm($products);

                    return [
                        'products' => $products->map(fn ($product) => $this->mapProductSummary($product))->values(),
                        'view_all_url' => $firstSharedTerm ? $this->buildFilterUrl($firstSharedTerm) : null,
                    ];
                }
            ),

            // HATEOAS links
            '_links' => [
                'self' => [
                    'href' => route('api.v1.products.show', ['slug' => $this->slug]),
                    'method' => 'GET',
                ],
                'list' => [
                    'href' => route('api.v1.products.index'),
                    'method' => 'GET',
                ],
                'category' => $this->when($this->relationLoaded('categories') && $this->categories->isNotEmpty(), [
                    'href' => route('api.v1.products.index', ['category' => $this->categories->pluck('id')->toArray()]),
                    'method' => 'GET',
                ]),
                'type' => $this->when($this->type, [
                    'href' => route('api.v1.products.index', ['type' => [$this->type->id]]),
                    'method' => 'GET',
                ]),
                'brand' => $this->when($this->relationLoaded('terms'), function () {
                    $brand = $this->primaryTerm('brand');

                    return $brand ? [
                        'href' => route('api.v1.products.index', ['terms' => ['brand' => [$brand->id]]]),
                        'method' => 'GET',
                    ] : null;
                }),
                'related' => $this->when($request->routeIs('api.v1.products.show'), [
                    'href' => route('api.v1.products.index', [
                        'category' => $this->relationLoaded('categories') && $this->categories->isNotEmpty()
                            ? $this->categories->pluck('id')->toArray()
                            : null,
                        'per_page' => 6,
                    ]),
                    'method' => 'GET',
                ]),
            ],
        ];
    }

    /**
     * Transform extra_attrs to include icon_url from CatalogAttributeGroup.
     *
     * @return array<string, array{label: string, value: string|int|float, type: string, icon_url: string|null}>
     */
    protected function transformExtraAttrs(): array
    {
        $extraAttrs = $this->extra_attrs ?? [];

        if (empty($extraAttrs)) {
            return [];
        }

        $codes = array_keys($extraAttrs);

        $iconMap = \App\Models\CatalogAttributeGroup::query()
            ->whereIn('code', $codes)
            ->pluck('icon_path', 'code')
            ->toArray();

        $transformed = [];
        foreach ($extraAttrs as $code => $attr) {
            $iconPath = $iconMap[$code] ?? null;
            $transformed[$code] = [
                'label' => $attr['label'] ?? $code,
                'value' => $attr['value'] ?? '',
                'type' => $attr['type'] ?? 'text',
                'icon_url' => $iconPath ? Storage::disk('public')->url($iconPath) : null,
            ];
        }

        return $transformed;
    }

    /**
     * Map product summary for related products
     */
    protected function mapProductSummary($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'original_price' => $product->original_price,
            'discount_percent' => $product->discount_percent,
            'show_contact_cta' => $product->should_show_contact_cta,
            'main_image_url' => $product->cover_image_url ?: '/placeholder/wine-bottle.svg',
            'gallery' => $product->gallery_for_output,
            'brand_term' => ($brand = $product->primaryTerm('brand')) ? [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
            ] : null,
            'country_term' => ($country = $product->primaryTerm('origin')) ? [
                'id' => $country->id,
                'name' => $country->name,
                'slug' => $country->slug,
            ] : null,
            'category' => $product->categories->isNotEmpty() ? [
                'id' => $product->categories->first()->id,
                'name' => $product->categories->first()->name,
                'slug' => $product->categories->first()->slug,
            ] : null,
            'type' => $product->type ? [
                'id' => $product->type->id,
                'name' => $product->type->name,
                'slug' => $product->type->slug,
            ] : null,
            'badges' => $product->badges ?? [],
        ];
    }

    /**
     * Get first shared term between current product and related products
     */
    protected function getFirstSharedTerm($products)
    {
        // Get all terms of current product (excluding brand)
        $currentProductTerms = $this->terms
            ->filter(function ($term) {
                return $term->group && $term->group->code !== 'brand';
            });

        // Find first matching term
        foreach ($currentProductTerms as $term) {
            return $term; // Return first term found
        }

        return null;
    }

    /**
     * Build filter URL based on term
     */
    protected function buildFilterUrl($term): string
    {
        if (! $term || ! $term->group) {
            return '/filter';
        }

        $groupCode = $term->group->code;

        // Map group code to filter param
        $paramMap = [
            'grape' => 'grape',
            'origin' => 'origin',
            'type' => 'type',
        ];

        $param = $paramMap[$groupCode] ?? $groupCode;

        return '/filter?'.$param.'='.$term->id;
    }

    /**
     * Build breadcrumbs for the product.
     */
    protected function buildBreadcrumbs(): array
    {
        $breadcrumbs = [];

        if ($this->relationLoaded('categories') && $this->categories->isNotEmpty()) {
            $firstCategory = $this->categories->first();
            $breadcrumbs[] = [
                'label' => $firstCategory->name,
                'href' => route('api.v1.products.index', ['category' => [$firstCategory->id]]),
            ];
        }

        if ($this->type) {
            $breadcrumbs[] = [
                'label' => $this->type->name,
                'href' => route('api.v1.products.index', ['type' => [$this->type->id]]),
            ];
        }

        if ($this->relationLoaded('terms')) {
            $brand = $this->primaryTerm('brand');
            if ($brand) {
                $breadcrumbs[] = [
                    'label' => $brand->name,
                    'href' => route('api.v1.products.index', ['terms' => ['brand' => [$brand->id]]]),
                ];
            }
        }

        return $breadcrumbs;
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => 'v1',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
