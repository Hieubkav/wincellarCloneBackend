<?php

namespace App\Observers;

use App\Models\ProductType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductTypeObserver
{
    public function creating(ProductType $productType): void
    {
        if (empty($productType->slug)) {
            $productType->slug = $this->generateUniqueSlug($productType->name);
        }

        if ($productType->order === null) {
            $productType->order = ProductType::max('order') + 1;
        }
    }

    public function created(ProductType $productType): void
    {
        $this->clearFilterCache();
    }

    public function updating(ProductType $productType): void
    {
        if ($productType->isDirty('name')) {
            $productType->slug = $this->generateUniqueSlug($productType->name, $productType->id);
        }
    }

    public function updated(ProductType $productType): void
    {
        $this->clearFilterCache();
    }

    public function deleted(ProductType $productType): void
    {
        $this->clearFilterCache();
    }

    private function clearFilterCache(): void
    {
        Cache::forget('product_filter_options_v2');
        Cache::forget('product_filter_options_v3');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = ProductType::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
