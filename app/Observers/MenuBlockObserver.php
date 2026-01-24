<?php

namespace App\Observers;

use App\Models\MenuBlock;
use Illuminate\Support\Facades\Cache;

class MenuBlockObserver
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

    public function creating(MenuBlock $menuBlock): void
    {
        if ($menuBlock->order === null) {
            $maxOrder = MenuBlock::where('menu_id', $menuBlock->menu_id)
                ->max('order') ?? -1;
            $menuBlock->order = $maxOrder + 1;
        }
    }

    public function created(MenuBlock $menuBlock): void
    {
        $this->incrementCacheVersion();
    }

    public function updated(MenuBlock $menuBlock): void
    {
        $this->incrementCacheVersion();
    }

    public function deleted(MenuBlock $menuBlock): void
    {
        $this->incrementCacheVersion();
    }

    public function restored(MenuBlock $menuBlock): void
    {
        $this->incrementCacheVersion();
    }

    public function forceDeleted(MenuBlock $menuBlock): void
    {
        $this->incrementCacheVersion();
    }
}
