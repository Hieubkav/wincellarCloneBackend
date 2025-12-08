<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Models\CatalogAttributeGroup;
use App\Models\CatalogTerm;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductFilterController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $typeId = $request->integer('type_id');
        $typeSlug = $request->input('type_slug');

        $type = null;
        if ($typeId) {
            $type = ProductType::query()->whereKey($typeId)->first();
        }
        if (!$type && $typeSlug) {
            $type = ProductType::query()->where('slug', $typeSlug)->first();
        }

        $cacheTtl = 3600;
        $cacheKey = 'product_filter_options_v4:' . ($type?->id ?? 'all');

        $data = cache()->remember($cacheKey, $cacheTtl, function () use ($type) {
            // Categories filtered by type (or all if none selected)
            $categoriesQuery = ProductCategory::query()
                ->where('active', true)
                ->orderBy('order')
                ->orderBy('id');

            if ($type) {
                $categoriesQuery->where(function ($query) use ($type) {
                    $query->where('type_id', $type->id)
                        ->orWhereNull('type_id');
                });
            }

            $categories = $categoriesQuery->get(['id', 'name', 'slug', 'type_id']);

            // All types (tabs/selector)
            $types = ProductType::query()
                ->where('active', true)
                ->orderBy('order')
                ->orderBy('id')
                ->get(['id', 'name', 'slug']);

            // Attribute groups by type (filterable only)
            $attributeGroups = $type
                ? $type->attributeGroups()
                    ->where('is_filterable', true)
                    ->with(['terms' => fn ($q) => $q->active()->orderBy('position')->orderBy('id')])
                    ->orderByPivot('position')
                    ->get(['catalog_attribute_groups.id', 'code', 'name', 'filter_type', 'input_type', 'display_config'])
                : CatalogAttributeGroup::query()
                    ->where('is_filterable', true)
                    ->with(['terms' => fn ($q) => $q->active()->orderBy('position')->orderBy('id')])
                    ->orderBy('position')
                    ->get(['id', 'code', 'name', 'filter_type', 'input_type', 'display_config']);

            $dynamicFilters = static::buildDynamicFilters($attributeGroups);

            // Price ranges
            $ranges = \DB::table('products')
                ->where('active', true)
                ->selectRaw('
                    MIN(price) as price_min,
                    MAX(price) as price_max
                ')
                ->first();

            return [
                'type' => $type ? [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                ] : null,
                'categories' => $categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'type_id' => $category->type_id,
                ])->values(),
                'types' => $types->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                ])->values(),
                'price' => [
                    'min' => (int) ($ranges->price_min ?? 0),
                    'max' => (int) ($ranges->price_max ?? 0),
                ],
                'attribute_filters' => $dynamicFilters,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * @param \Illuminate\Support\Collection<int, CatalogAttributeGroup> $attributeGroups
     * @return array<int, array<string, mixed>>
     */
    protected static function buildDynamicFilters(Collection $attributeGroups): array
    {
        $filters = [];

        foreach ($attributeGroups as $group) {
            $terms = $group->terms->map(fn (CatalogTerm $term) => [
                'id' => $term->id,
                'name' => $term->name,
                'slug' => $term->slug,
            ])->values();

            if ($terms->isEmpty()) {
                continue;
            }

            $displayConfig = $group->display_config;
            if (is_string($displayConfig)) {
                $displayConfig = json_decode($displayConfig, true) ?? [];
            }

            $filters[] = [
                'code' => $group->code,
                'name' => $group->name,
                'filter_type' => $group->filter_type,
                'input_type' => $group->input_type,
                'display_config' => $displayConfig,
                'options' => $terms,
            ];
        }

        return $filters;
    }
}
