<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'alcohol_percent' => $this->alcohol_percent,
            'volume_ml' => $this->volume_ml,
            'badges' => $this->badges ?? [],
            
            // Category and Type
            'category' => $this->when($this->relationLoaded('productCategory') && $this->productCategory, [
                'id' => $this->productCategory->id,
                'name' => $this->productCategory->name,
                'slug' => $this->productCategory->slug,
            ]),
            
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
                'category' => $this->when($this->productCategory, [
                    'href' => route('api.v1.products.index', ['category' => [$this->productCategory->id]]),
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
                        'category' => $this->productCategory ? [$this->productCategory->id] : null,
                        'per_page' => 6,
                    ]),
                    'method' => 'GET',
                ]),
            ],
        ];
    }

    /**
     * Build breadcrumbs for the product.
     */
    protected function buildBreadcrumbs(): array
    {
        $breadcrumbs = [];

        if ($this->productCategory) {
            $breadcrumbs[] = [
                'label' => $this->productCategory->name,
                'href' => route('api.v1.products.index', ['category' => [$this->productCategory->id]]),
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
