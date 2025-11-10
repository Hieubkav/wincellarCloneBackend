<?php

namespace App\Support\Product;

use App\Models\CatalogTerm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductFacetAggregator
{
    public function __construct(private Builder $baseQuery)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return [
            'facets' => [
                'categories' => $this->categories(),
                'types' => $this->types(),
                'brands' => $this->termsByGroup('brand'),
                'grapes' => $this->termsByGroup('grape'),
                'origins' => $this->origins(),
            ],
            'ranges' => [
                'price' => $this->priceRange(),
                'alcohol' => $this->alcoholRange(),
            ],
        ];
    }

    /**
     * @return array<int, array{id:int,name:string,slug:string,count:int}>
     */
    private function categories(): array
    {
        $builder = $this->newAggregateBuilder();

        return $builder
            ->join('product_category_product', 'product_category_product.product_id', '=', 'products.id')
            ->join('product_categories', 'product_categories.id', '=', 'product_category_product.product_category_id')
            ->where('product_categories.active', true)
            ->select([
                'product_categories.id',
                'product_categories.name',
                'product_categories.slug',
                DB::raw('COUNT(DISTINCT products.id) as total'),
            ])
            ->groupBy('product_categories.id', 'product_categories.name', 'product_categories.slug')
            ->orderByRaw('total desc')
            ->orderBy('product_categories.order')
            ->orderBy('product_categories.id')
            ->get()
            ->map(static function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'slug' => (string) $row->slug,
                    'count' => (int) $row->total,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{id:int,name:string,slug:string,count:int}>
     */
    private function types(): array
    {
        $builder = $this->newAggregateBuilder();

        return $builder
            ->join('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('product_types.active', true)
            ->select([
                'product_types.id',
                'product_types.name',
                'product_types.slug',
                DB::raw('COUNT(DISTINCT products.id) as total'),
            ])
            ->groupBy('product_types.id', 'product_types.name', 'product_types.slug')
            ->orderByDesc('total')
            ->orderBy('product_types.order')
            ->orderBy('product_types.id')
            ->get()
            ->map(static function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'slug' => (string) $row->slug,
                    'count' => (int) $row->total,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{id:int,name:string,slug:string,count:int}>
     */
    private function termsByGroup(string $groupCode): array
    {
        $builder = $this->newAggregateBuilder();

        return $builder
            ->join('product_term_assignments as pta', 'pta.product_id', '=', 'products.id')
            ->join('catalog_terms', 'catalog_terms.id', '=', 'pta.term_id')
            ->join('catalog_attribute_groups as cag', 'cag.id', '=', 'catalog_terms.group_id')
            ->where('catalog_terms.is_active', true)
            ->where('cag.code', $groupCode)
            ->select([
                'catalog_terms.id',
                'catalog_terms.name',
                'catalog_terms.slug',
                DB::raw('COUNT(DISTINCT products.id) as total'),
            ])
            ->groupBy('catalog_terms.id', 'catalog_terms.name', 'catalog_terms.slug')
            ->orderByDesc('total')
            ->orderBy('catalog_terms.position')
            ->orderBy('catalog_terms.id')
            ->limit(30)
            ->get()
            ->map(static function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'slug' => (string) $row->slug,
                    'count' => (int) $row->total,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{id:int,name:string,slug:string,count:int}>
     */
    private function origins(): array
    {
        $builder = $this->newAggregateBuilder();

        return $builder
            ->join('product_term_assignments as pta', 'pta.product_id', '=', 'products.id')
            ->join('catalog_terms', 'catalog_terms.id', '=', 'pta.term_id')
            ->join('catalog_attribute_groups as cag', 'cag.id', '=', 'catalog_terms.group_id')
            ->where('catalog_terms.is_active', true)
            ->where('cag.code', 'origin')
            ->select([
                'catalog_terms.id',
                'catalog_terms.name',
                'catalog_terms.slug',
                DB::raw('COUNT(DISTINCT products.id) as total'),
            ])
            ->groupBy(
                'catalog_terms.id',
                'catalog_terms.name',
                'catalog_terms.slug'
            )
            ->orderByDesc('total')
            ->orderBy('catalog_terms.position')
            ->orderBy('catalog_terms.id')
            ->limit(30)
            ->get()
            ->map(static function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'slug' => (string) $row->slug,
                    'count' => (int) $row->total,
                ];
            })
            ->all();
    }

    /**
     * @return array<string, int|null>
     */
    private function priceRange(): array
    {
        $builder = $this->newAggregateBuilder();

        $min = (clone $builder)->min('products.price');
        $max = (clone $builder)->max('products.price');

        return [
            'min' => $min !== null ? (int) $min : null,
            'max' => $max !== null ? (int) $max : null,
        ];
    }

    /**
     * @return array<string, float|null>
     */
    private function alcoholRange(): array
    {
        $builder = $this->newAggregateBuilder()->whereNotNull('products.alcohol_percent');

        $min = (clone $builder)->min('products.alcohol_percent');
        $max = (clone $builder)->max('products.alcohol_percent');

        return [
            'min' => $min !== null ? (float) $min : null,
            'max' => $max !== null ? (float) $max : null,
        ];
    }

    private function newAggregateBuilder(): Builder
    {
        /** @var Builder $clone */
        $clone = (clone $this->baseQuery)->cloneWithout(['columns', 'orders', 'limit', 'offset']);
        $clone = $clone->cloneWithoutBindings(['select', 'order']);

        return $clone->select('products.id')->distinct();
    }
}
