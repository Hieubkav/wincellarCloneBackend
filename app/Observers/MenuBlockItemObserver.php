<?php

namespace App\Observers;

use App\Models\MenuBlockItem;
use Illuminate\Support\Facades\Cache;

class MenuBlockItemObserver
{
    /**
     * Increment API cache version when menu data changes
     */
    private function incrementCacheVersion(): void
    {
        $version = (int) Cache::get('api_cache_version', 0);
        Cache::put('api_cache_version', $version + 1);
        Cache::put('last_cache_clear', now()->toIso8601String());
    }

    public function created(MenuBlockItem $menuBlockItem): void
    {
        $this->incrementCacheVersion();
    }

    public function updated(MenuBlockItem $menuBlockItem): void
    {
        $this->incrementCacheVersion();
    }

    public function deleted(MenuBlockItem $menuBlockItem): void
    {
        $this->incrementCacheVersion();
    }

    public function restored(MenuBlockItem $menuBlockItem): void
    {
        $this->incrementCacheVersion();
    }

    public function forceDeleted(MenuBlockItem $menuBlockItem): void
    {
        $this->incrementCacheVersion();
    }
}
