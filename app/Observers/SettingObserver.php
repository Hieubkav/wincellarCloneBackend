<?php

namespace App\Observers;

use App\Models\Setting;
use App\Support\Cache\ApiCacheVersionManager;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
    /**
     * Increment API cache version and clear settings cache when settings change
     */
    private function invalidateCache(string $action): void
    {
        Cache::forget('api:v1:settings');

        $oldVersion = (int) Cache::get('api_cache_version', 0);
        $newVersion = ApiCacheVersionManager::bumpApiVersion();
        $imageProxyVersion = ApiCacheVersionManager::bumpImageProxyVersion();

        \Log::info('Settings cache invalidated', [
            'action' => $action,
            'cache_version' => [
                'old' => $oldVersion,
                'new' => $newVersion,
            ],
            'image_proxy_cache_version' => $imageProxyVersion,
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
