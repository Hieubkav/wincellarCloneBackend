<?php

namespace App\Observers;

use App\Models\YourModel;
use Illuminate\Support\Facades\Cache;

/**
 * Observer Template for Cache Invalidation
 * 
 * USAGE:
 * 1. Copy this file
 * 2. Replace "YourModel" with your actual model name
 * 3. Add #[ObservedBy(YourModelObserver::class)] to your model
 * 4. Implement your specific logic in creating/updating methods
 */
class YourModelObserver
{
    /**
     * Increment API cache version when data changes
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
                'model' => 'YourModel',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the YourModel "creating" event.
     * 
     * Use this for:
     * - Auto-set order
     * - Auto-generate slugs
     * - Default values
     */
    public function creating(YourModel $model): void
    {
        // Your logic here (optional)
    }

    /**
     * Handle the YourModel "created" event.
     */
    public function created(YourModel $model): void
    {
        $this->incrementCacheVersion();
    }

    /**
     * Handle the YourModel "updating" event.
     * 
     * Use this for:
     * - Selective revalidation (check isDirty)
     * - Validation before save
     */
    public function updating(YourModel $model): void
    {
        // Your logic here (optional)
    }

    /**
     * Handle the YourModel "updated" event.
     */
    public function updated(YourModel $model): void
    {
        $this->incrementCacheVersion();
    }

    /**
     * Handle the YourModel "deleted" event.
     */
    public function deleted(YourModel $model): void
    {
        $this->incrementCacheVersion();
    }

    /**
     * Handle the YourModel "restored" event.
     */
    public function restored(YourModel $model): void
    {
        $this->incrementCacheVersion();
    }

    /**
     * Handle the YourModel "force deleted" event.
     */
    public function forceDeleted(YourModel $model): void
    {
        $this->incrementCacheVersion();
    }
}
