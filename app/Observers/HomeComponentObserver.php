<?php

namespace App\Observers;

use App\Models\HomeComponent;
use Illuminate\Support\Facades\Cache;

class HomeComponentObserver
{
    public function creating(HomeComponent $homeComponent): void
    {
        if ($homeComponent->order === null) {
            $maxOrder = HomeComponent::max('order') ?? -1;
            $homeComponent->order = $maxOrder + 1;
        }
    }

    public function created(HomeComponent $homeComponent): void
    {
        $this->incrementCacheVersion();
    }

    public function updated(HomeComponent $homeComponent): void
    {
        $this->incrementCacheVersion();
    }

    public function deleted(HomeComponent $homeComponent): void
    {
        $this->incrementCacheVersion();
    }

    private function incrementCacheVersion(): void
    {
        $version = (int) Cache::get('api_cache_version', 0);
        Cache::put('api_cache_version', $version + 1);
        Cache::put('last_cache_clear', now()->toIso8601String());
    }
}
