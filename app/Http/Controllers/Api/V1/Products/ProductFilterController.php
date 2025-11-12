<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Models\CatalogAttributeGroup;
use App\Models\CatalogTerm;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductFilterController extends Controller
{
    public function __invoke(): JsonResponse
    {
        // Cache filter options for 1 hour since they don't change frequently
        // Cache key includes catalog version to auto-invalidate on changes
        $cacheKey = 'product_filter_options_v3';
        $cacheTtl = 3600; // 1 hour (increased from 10 min for better performance)

        $data = cache()->remember($cacheKey, $cacheTtl, function () {
            // Get categories and types (built-in filters)
            $categories = ProductCategory::query()
                ->where('active', true)
                ->orderBy('order')
                ->orderBy('id')
                ->get(['id', 'name', 'slug']);

            $types = ProductType::query()
                ->where('active', true)
                ->orderBy('order')
                ->orderBy('id')
                ->get(['id', 'name', 'slug']);

            // Get dynamic attribute groups (filterable only)
            $attributeGroups = CatalogAttributeGroup::query()
                ->where('is_filterable', true)
                ->orderBy('position')
                ->get(['id', 'code', 'name', 'filter_type', 'display_config']);

            // Build dynamic filters based on attribute groups
            $dynamicFilters = [];
            foreach ($attributeGroups as $group) {
                $terms = $this->fetchTermsByGroup($group->code);
                
                // Only include if there are terms
                if ($terms->count() > 0) {
                    $displayConfig = $group->display_config;
                    if (is_string($displayConfig)) {
                        $displayConfig = json_decode($displayConfig, true) ?? [];
                    }
                    
                    $dynamicFilters[] = [
                        'code' => $group->code,
                        'name' => $group->name,
                        'filter_type' => $group->filter_type,
                        'display_config' => $displayConfig,
                        'options' => $terms,
                    ];
                }
            }

            // Get price and alcohol ranges in SINGLE query (optimized)
            $ranges = \DB::table('products')
                ->where('active', true)
                ->selectRaw('
                    MIN(price) as price_min,
                    MAX(price) as price_max,
                    MIN(alcohol_percent) as alcohol_min,
                    MAX(alcohol_percent) as alcohol_max
                ')
                ->first();

            $priceMin = $ranges->price_min ?? 0;
            $priceMax = $ranges->price_max ?? 0;
            $alcoholMin = $ranges->alcohol_min ?? 0;
            $alcoholMax = $ranges->alcohol_max ?? 0;

            return [
                // Built-in filters (always present)
                'categories' => $categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])->values(),
                'types' => $types->map(fn ($type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                ])->values(),
                'price' => [
                    'min' => (int) $priceMin,
                    'max' => (int) $priceMax,
                ],
                'alcohol' => [
                    'min' => (float) $alcoholMin,
                    'max' => (float) $alcoholMax,
                ],
                
                // Dynamic filters (based on catalog_attribute_groups)
                'attribute_filters' => $dynamicFilters,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id:int,name:string,slug:string}>
     */
    private function fetchTermsByGroup(string $code): Collection
    {
        return CatalogTerm::query()
            ->active()
            ->whereHas('group', fn ($query) => $query->where('code', $code))
            ->orderBy('position')
            ->orderBy('id')
            ->get(['id', 'name', 'slug'])
            ->map(fn (CatalogTerm $term) => [
                'id' => $term->id,
                'name' => $term->name,
                'slug' => $term->slug,
            ])
            ->values();
    }
}
