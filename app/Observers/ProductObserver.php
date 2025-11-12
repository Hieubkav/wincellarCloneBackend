<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\Product\ProductCacheManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Increment API cache version when product data changes
     * Also flush product caches (Priority 3 optimization)
     */
    private function incrementCacheVersion(): void
    {
        $version = (int) Cache::get('api_cache_version', 0);
        Cache::put('api_cache_version', $version + 1);
        Cache::put('last_cache_clear', now()->toIso8601String());

        // Priority 3: Flush product query caches using tag-based invalidation
        ProductCacheManager::flushAll();
    }
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

    public function created(Product $product): void
    {
        $this->incrementCacheVersion();
    }

    public function updated(Product $product): void
    {
        $this->incrementCacheVersion();
    }

    public function deleted(Product $product): void
    {
        $this->incrementCacheVersion();
    }

    public function restored(Product $product): void
    {
        $this->incrementCacheVersion();
    }

    public function forceDeleted(Product $product): void
    {
        $this->incrementCacheVersion();
    }
}
