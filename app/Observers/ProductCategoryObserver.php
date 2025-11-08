<?php

namespace App\Observers;

use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategoryObserver
{
    public function creating(ProductCategory $category): void
    {
        if (empty($category->slug)) {
            $category->slug = $this->generateUniqueSlug($category->name);
        }

        if ($category->order === null) {
            $category->order = $this->getNextOrder();
        }
    }

    public function updating(ProductCategory $category): void
    {
        if ($category->isDirty('name')) {
            $category->slug = $this->generateUniqueSlug($category->name, $category->id);
        }
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = ProductCategory::where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function getNextOrder(): int
    {
        return ProductCategory::max('order') + 1 ?? 0;
    }
}
