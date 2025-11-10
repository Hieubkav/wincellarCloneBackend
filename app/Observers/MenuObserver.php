<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    /**
     * Increment API cache version when menu data changes
     * AND trigger Next.js on-demand revalidation
     */
    private function incrementCacheVersion(): void
    {
        $version = (int) Cache::get('api_cache_version', 0);
        Cache::put('api_cache_version', $version + 1);
        Cache::put('last_cache_clear', now()->toIso8601String());
        
        // Trigger Next.js revalidation ngay lập tức
        try {
            app(\App\Services\RevalidationService::class)->revalidateAll();
        } catch (\Throwable $e) {
            \Log::warning('Failed to trigger Next.js revalidation', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function created(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }

    public function updated(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }

    public function deleted(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }

    public function restored(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }

    public function forceDeleted(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }
}
