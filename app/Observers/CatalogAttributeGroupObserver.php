<?php

namespace App\Observers;

use App\Models\CatalogAttributeGroup;
use Illuminate\Support\Facades\Storage;

class CatalogAttributeGroupObserver
{
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

    /**
     * Handle the CatalogAttributeGroup "deleted" event.
     * Xóa icon khi xóa record
     */
    public function deleted(CatalogAttributeGroup $catalogAttributeGroup): void
    {
        if ($catalogAttributeGroup->icon_path && Storage::disk('public')->exists($catalogAttributeGroup->icon_path)) {
            Storage::disk('public')->delete($catalogAttributeGroup->icon_path);
        }
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
    }
}
