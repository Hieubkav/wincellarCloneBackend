<?php

namespace App\Observers;

use App\Models\CatalogAttributeGroup;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatalogAttributeGroupObserver
{
    public function created(CatalogAttributeGroup $catalogAttributeGroup): void
    {
        $this->clearFilterCache();
    }

    /**
     * Handle the CatalogAttributeGroup "updating" event.
     * Xóa icon cũ khi update icon mới
     */
    public function updating(CatalogAttributeGroup $catalogAttributeGroup): void
    {
        if ($catalogAttributeGroup->isDirty('icon_path')) {
            $oldIconPath = $catalogAttributeGroup->getOriginal('icon_path');
            if ($oldIconPath && Storage::disk('public')->exists($oldIconPath)) {
                Storage::disk('public')->delete($oldIconPath);
            }
        }
    }

    public function updated(CatalogAttributeGroup $catalogAttributeGroup): void
    {
        // Sync label trong extra_attrs của products khi đổi tên group
        if ($catalogAttributeGroup->wasChanged('name') && $catalogAttributeGroup->filter_type === 'nhap_tay') {
            $this->syncExtraAttrsLabel($catalogAttributeGroup);
        }

        $this->clearFilterCache();
    }

    /**
     * Cập nhật label trong extra_attrs của tất cả products khi đổi tên attribute group
     */
    protected function syncExtraAttrsLabel(CatalogAttributeGroup $group): void
    {
        $code = $group->code;
        $newName = $group->name;
        $jsonPath = '$."' . $code . '".label';

        DB::table('products')
            ->whereRaw('JSON_EXTRACT(extra_attrs, ?) IS NOT NULL', ['$."' . $code . '"'])
            ->update([
                'extra_attrs' => DB::raw("JSON_SET(extra_attrs, '{$jsonPath}', " . DB::getPdo()->quote($newName) . ")"),
            ]);
    }

    /**
     * Handle the CatalogAttributeGroup "deleted" event.
     * Xóa icon khi xóa record
     */
    public function deleted(CatalogAttributeGroup $catalogAttributeGroup): void
    {
        if ($catalogAttributeGroup->icon_path && Storage::disk('public')->exists($catalogAttributeGroup->icon_path)) {
            Storage::disk('public')->delete($catalogAttributeGroup->icon_path);
        }
        
        $this->clearFilterCache();
    }

    /**
     * Handle the CatalogAttributeGroup "force deleted" event.
     * Xóa icon khi force delete
     */
    public function forceDeleted(CatalogAttributeGroup $catalogAttributeGroup): void
    {
        if ($catalogAttributeGroup->icon_path && Storage::disk('public')->exists($catalogAttributeGroup->icon_path)) {
            Storage::disk('public')->delete($catalogAttributeGroup->icon_path);
        }
        
        $this->clearFilterCache();
    }

    private function clearFilterCache(): void
    {
        // Clear tất cả versions của filter cache
        $patterns = ['product_filter_options_v2', 'product_filter_options_v3', 'product_filter_options_v5'];
        foreach ($patterns as $pattern) {
            // Clear cache cho tất cả types (all, và từng type_id)
            Cache::forget($pattern . ':all');
            // Xóa cache cho các type cụ thể (giả sử có tối đa 100 types)
            for ($i = 1; $i <= 100; $i++) {
                Cache::forget($pattern . ':' . $i);
            }
        }
    }
}
