<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     * Tự động sinh slug từ tên sản phẩm khi tạo mới
     */
    public function creating(Product $product): void
    {
        if (empty($product->slug)) {
            $product->slug = $this->generateUniqueSlug($product->name);
        }

        $this->generateSeoFields($product);
    }

    /**
     * Handle the Product "updating" event.
     * Tự động cập nhật slug khi tên sản phẩm thay đổi
     */
    public function updating(Product $product): void
    {
        if ($product->isDirty('name')) {
            $product->slug = $this->generateUniqueSlug($product->name, $product->id);
            $this->generateSeoFields($product);
        }
    }

    /**
     * Generate SEO fields if empty
     */
    private function generateSeoFields(Product $product): void
    {
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }

        if (empty($product->meta_description) && !empty($product->description)) {
            $product->meta_description = Str::limit($product->description, 155);
        }
    }

    /**
     * Generate unique slug for product
     */
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

    /**
     * Check if slug exists
     */
    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = Product::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
