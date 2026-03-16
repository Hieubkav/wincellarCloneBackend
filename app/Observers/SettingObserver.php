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
    private function invalidateCache(Setting $setting, string $action): void
    {
        Cache::forget('api:v1:settings');

        $oldVersion = (int) Cache::get('api_cache_version', 0);
        $newVersion = ApiCacheVersionManager::bumpApiVersion();
        $watermarkChanged = $this->watermarkFieldsChanged($setting);

        \Log::info('Settings cache invalidated', [
            'action' => $action,
            'cache_version' => [
                'old' => $oldVersion,
                'new' => $newVersion,
            ],
            'watermark_changed' => $watermarkChanged,
            'timestamp' => now()->toIso8601String(),
        ]);

        try {
            app(\App\Services\RevalidationService::class)->revalidateSettings();
            \Log::info('Next.js settings revalidation triggered successfully', [
                'action' => $action,
                'watermark_changed' => $watermarkChanged,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to trigger Next.js settings revalidation', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function created(Setting $setting): void
    {
        $this->invalidateCache($setting, 'created');
    }

    public function updated(Setting $setting): void
    {
        $this->invalidateCache($setting, 'updated');
    }

    public function deleted(Setting $setting): void
    {
        $this->invalidateCache($setting, 'deleted');
    }

    public function restored(Setting $setting): void
    {
        $this->invalidateCache($setting, 'restored');
    }

    public function forceDeleted(Setting $setting): void
    {
        $this->invalidateCache($setting, 'force_deleted');
    }

    private function watermarkFieldsChanged(Setting $setting): bool
    {
        if (! $setting->wasChanged()) {
            return false;
        }

        return count(array_intersect(array_keys($setting->getChanges()), [
            'product_watermark_image_id',
            'product_watermark_position',
            'product_watermark_size',
            'product_watermark_type',
            'product_watermark_text',
            'product_watermark_text_size',
            'product_watermark_text_position',
            'product_watermark_text_opacity',
            'product_watermark_text_repeat',
        ])) > 0;
    }
}
