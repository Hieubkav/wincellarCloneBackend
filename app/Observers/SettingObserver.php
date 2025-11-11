<?php

namespace App\Observers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
    /**
     * Increment API cache version and clear settings cache when settings change
     */
    private function invalidateCache(string $action): void
    {
        // Clear settings cache
        Cache::forget('api:v1:settings');
        
        // Increment global cache version
        $oldVersion = (int) Cache::get('api_cache_version', 0);
        $newVersion = $oldVersion + 1;
        Cache::put('api_cache_version', $newVersion);
        Cache::put('last_cache_clear', now()->toIso8601String());
        
        // Log successful invalidation
        \Log::info('Settings cache invalidated', [
            'action' => $action,
            'cache_version' => [
                'old' => $oldVersion,
                'new' => $newVersion,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
        
        // Trigger Next.js revalidation immediately
        try {
            app(\App\Services\RevalidationService::class)->revalidateAll();
            \Log::info('Next.js revalidation triggered successfully', [
                'action' => $action,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to trigger Next.js revalidation', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function created(Setting $setting): void
    {
        $this->invalidateCache('created');
    }

    public function updated(Setting $setting): void
    {
        $this->invalidateCache('updated');
    }

    public function deleted(Setting $setting): void
    {
        $this->invalidateCache('deleted');
    }

    public function restored(Setting $setting): void
    {
        $this->invalidateCache('restored');
    }

    public function forceDeleted(Setting $setting): void
    {
        $this->invalidateCache('force_deleted');
    }
}
