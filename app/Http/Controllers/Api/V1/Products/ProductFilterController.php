<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Models\CatalogTerm;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ProductFilterController extends Controller
{
    public function __invoke(): JsonResponse
    {
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

        $brands = $this->fetchTermsByGroup('brand');
        $grapes = $this->fetchTermsByGroup('grape');

        $countries = CatalogTerm::query()
            ->with(['children' => fn ($query) => $query->active()->orderBy('position')->orderBy('id')])
            ->active()
            ->whereNull('parent_id')
            ->whereHas('group', fn ($query) => $query->where('code', 'origin'))
            ->orderBy('position')
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'parent_id', 'position']);

        $priceMin = Product::query()->active()->min('price') ?? 0;
        $priceMax = Product::query()->active()->max('price') ?? 0;

        $alcoholMin = Product::query()->active()->min('alcohol_percent') ?? 0;
        $alcoholMax = Product::query()->active()->max('alcohol_percent') ?? 0;

        return response()->json([
            'data' => [
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
                'brands' => $brands,
                'grapes' => $grapes,
                'countries' => $countries->map(function (CatalogTerm $country) {
                    return [
                        'id' => $country->id,
                        'name' => $country->name,
                        'slug' => $country->slug,
                        'regions' => $country->children
                            ->map(fn (CatalogTerm $region) => [
                                'id' => $region->id,
                                'name' => $region->name,
                                'slug' => $region->slug,
                            ])
                            ->values(),
                    ];
                })->values(),
                'price' => [
                    'min' => (int) $priceMin,
                    'max' => (int) $priceMax,
                ],
                'alcohol' => [
                    'min' => (float) $alcoholMin,
                    'max' => (float) $alcoholMax,
                ],
            ],
        ]);
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
