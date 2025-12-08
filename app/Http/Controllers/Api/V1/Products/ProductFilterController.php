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
    public function index(Request $request): JsonResponse
    {
        $typeId = $request->integer('type_id');
        $typeSlug = $request->input('type_slug');

        $type = null;
        if ($typeId) {
            $type = ProductType::query()->whereKey($typeId)->first();
        }
        if (! $type && $typeSlug) {
            $type = ProductType::query()->where('slug', $typeSlug)->first();
        }

        $cacheTtl = 3600;
        $cacheKey = 'product_filter_options_v5:'.($type?->id ?? 'all');

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
            // Nếu có type → show filters của type đó
            // Nếu không có type → show ONLY common filters (intersection của tất cả types)
            $attributeGroups = $type
                ? $type->attributeGroups()
                    ->where('is_filterable', true)
                    ->with(['terms' => fn ($q) => $q->active()->orderBy('position')->orderBy('id')])
                    ->orderByPivot('position')
                    ->get(['catalog_attribute_groups.id', 'code', 'name', 'filter_type', 'input_type', 'display_config', 'icon_path'])
                : static::getCommonAttributeGroups($types);

            $dynamicFilters = static::buildDynamicFilters($attributeGroups, $type);

            // Price ranges
            $priceQuery = \DB::table('products')
                ->where('active', true);

            if ($type) {
                $priceQuery->where('type_id', $type->id);
            }

            $ranges = $priceQuery->selectRaw('
                MIN(price) as price_min,
                MAX(price) as price_max
            ')->first();

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
     * Lấy attribute groups CHUNG của tất cả product types active (intersection).
     * Chỉ trả về groups xuất hiện trong TẤT CẢ types.
     * Nếu không có type nào hoặc không có group chung → trả về empty collection.
     *
     * @param  \Illuminate\Support\Collection<int, ProductType>  $types
     * @return \Illuminate\Support\Collection<int, CatalogAttributeGroup>
     */
    protected static function getCommonAttributeGroups(Collection $types): Collection
    {
        $activeTypeCount = $types->count();

        if ($activeTypeCount === 0) {
            return collect();
        }

        $activeTypeIds = $types->pluck('id');

        // Tìm group_ids xuất hiện trong TẤT CẢ types active
        $commonGroupIds = \DB::table('catalog_attribute_group_product_type')
            ->whereIn('type_id', $activeTypeIds)
            ->groupBy('group_id')
            ->havingRaw('COUNT(DISTINCT type_id) = ?', [$activeTypeCount])
            ->pluck('group_id');

        if ($commonGroupIds->isEmpty()) {
            return collect();
        }

        return CatalogAttributeGroup::query()
            ->whereIn('id', $commonGroupIds)
            ->where('is_filterable', true)
            ->with(['terms' => fn ($q) => $q->active()->orderBy('position')->orderBy('id')])
            ->orderBy('position')
            ->get(['id', 'code', 'name', 'filter_type', 'input_type', 'display_config', 'icon_path']);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CatalogAttributeGroup>  $attributeGroups
     * @param  ProductType|null  $type  Filter counts theo type nếu có
     * @return array<int, array<string, mixed>>
     */
    protected static function buildDynamicFilters(Collection $attributeGroups, ?ProductType $type = null): array
    {
        $filters = [];

        // Lấy term counts một lần cho tất cả terms (tối ưu performance)
        // Filter theo type nếu có để counts chính xác
        $termCounts = static::getTermProductCounts($type);

        foreach ($attributeGroups as $group) {
            // Nhập tay + số: trả về min/max từ extra_attrs
            if ($group->filter_type === 'nhap_tay' && $group->input_type === 'number') {
                // Build JSON path với quoted key để xử lý ký tự đặc biệt (e.g., "do-cao")
                $jsonPath = '$."'.$group->code.'"';
                $jsonPathValue = '$."'.$group->code.'".value';

                $statsQuery = \DB::table('products')
                    ->where('active', true)
                    ->whereRaw('JSON_EXTRACT(extra_attrs, ?) IS NOT NULL', [$jsonPath]);

                // Filter theo type nếu có
                if ($type) {
                    $statsQuery->where('type_id', $type->id);
                }

                $stats = $statsQuery->selectRaw(
                    'MIN(CAST(JSON_UNQUOTE(JSON_EXTRACT(extra_attrs, ?)) AS DECIMAL(10,2))) as min_val,
                        MAX(CAST(JSON_UNQUOTE(JSON_EXTRACT(extra_attrs, ?)) AS DECIMAL(10,2))) as max_val',
                    [$jsonPathValue, $jsonPathValue]
                )
                    ->first();

                if ($stats && ($stats->min_val !== null || $stats->max_val !== null)) {
                    $filters[] = [
                        'code' => $group->code,
                        'name' => $group->name,
                        'filter_type' => $group->filter_type,
                        'input_type' => $group->input_type,
                        'icon_url' => $group->icon_path ? asset('storage/' . $group->icon_path) : null,
                        'range' => [
                            'min' => (float) ($stats->min_val ?? 0),
                            'max' => (float) ($stats->max_val ?? 100),
                        ],
                        'options' => [],
                    ];
                }

                continue;
            }

            $terms = $group->terms->map(fn (CatalogTerm $term) => [
                'id' => $term->id,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => $termCounts[$term->id] ?? 0,
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
                'icon_url' => $group->icon_path ? asset('storage/' . $group->icon_path) : null,
                'options' => $terms,
            ];
        }

        return $filters;
    }

    /**
     * Đếm số sản phẩm active cho mỗi term.
     * Filter theo type nếu có để counts chính xác với filter set.
     *
     * @param  ProductType|null  $type  Filter theo type nếu có
     * @return array<int, int> Map của term_id => count
     */
    protected static function getTermProductCounts(?ProductType $type = null): array
    {
        $query = \DB::table('product_term_assignments as pta')
            ->join('products', 'products.id', '=', 'pta.product_id')
            ->where('products.active', true);

        // Filter theo type nếu có
        if ($type) {
            $query->where('products.type_id', $type->id);
        }

        return $query
            ->selectRaw('pta.term_id, COUNT(DISTINCT pta.product_id) as cnt')
            ->groupBy('pta.term_id')
            ->pluck('cnt', 'term_id')
            ->toArray();
    }
}
