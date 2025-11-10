<?php

namespace App\Observers;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImageObserver
{
    /**
     * Increment API cache version when image data changes
     */
    private function incrementCacheVersion(): void
    {
        $version = (int) Cache::get('api_cache_version', 0);
        Cache::put('api_cache_version', $version + 1);
        Cache::put('last_cache_clear', now()->toIso8601String());
    }
    /**
     * Handle the Image "creating" event.
     * Tự động set order và alt text cho ảnh mới
     */
    public function creating(Image $image): void
    {
        // Auto-assign order if not set
        if ($image->order === null && $image->model_type && $image->model_id) {
            $image->order = $this->findNextAvailableOrder($image);
        }
        
        // Auto-generate alt text
        if (empty($image->alt)) {
            $image->alt = $this->generateAltText($image);
        }
    }
    
    /**
     * Tìm order trống tiếp theo cho model
     */
    private function findNextAvailableOrder(Image $image): int
    {
        $nextOrder = 0;
        
        while (Image::query()
            ->where('model_type', $image->model_type)
            ->where('model_id', $image->model_id)
            ->where('order', $nextOrder)
            ->whereNull('deleted_at')  // Exclude soft deleted
            ->when($image->id, fn($q) => $q->where('id', '!=', $image->id))  // Exclude current image if updating
            ->exists()
        ) {
            $nextOrder++;
        }
        
        return $nextOrder;
    }

    /**
     * Handle the Image "updating" event.
     * - Xóa file ảnh cũ khi upload file mới
     * - Update alt text khi order thay đổi
     */
    public function updating(Image $image): void
    {
        // Xóa file cũ khi upload mới
        if ($image->isDirty('file_path')) {
            $oldFilePath = $image->getOriginal('file_path');
            $oldDisk = $image->getOriginal('disk') ?? 'public';

            if ($oldFilePath && Storage::disk($oldDisk)->exists($oldFilePath)) {
                Storage::disk($oldDisk)->delete($oldFilePath);
            }
        }

        // Update alt text khi order thay đổi
        if ($image->isDirty('order') && empty($image->alt)) {
            $image->alt = $this->generateAltText($image);
        }
    }

    /**
     * Handle the Image "saving" event.
     * Tự động lấy width, height, mime từ file khi tạo/cập nhật
     */
    public function saving(Image $image): void
    {
        if ($image->isDirty('file_path') && $image->file_path) {
            try {
                $disk = $image->disk ?? 'public';
                $fullPath = Storage::disk($disk)->path($image->file_path);

                if (file_exists($fullPath)) {
                    [$width, $height, $type] = getimagesize($fullPath);
                    
                    $image->width = $width;
                    $image->height = $height;
                    
                    $mimeTypes = [
                        IMAGETYPE_GIF => 'image/gif',
                        IMAGETYPE_JPEG => 'image/jpeg',
                        IMAGETYPE_PNG => 'image/png',
                        IMAGETYPE_WEBP => 'image/webp',
                        IMAGETYPE_BMP => 'image/bmp',
                    ];
                    
                    $image->mime = $mimeTypes[$type] ?? 'image/webp';
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to extract image dimensions', [
                    'image_id' => $image->id,
                    'file_path' => $image->file_path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Tự động generate alt text từ model name + order
     */
    private function generateAltText(Image $image): string
    {
        if (!$image->model_type || !$image->model_id) {
            return 'Hình ảnh';
        }

        try {
            $model = $image->model_type::find($image->model_id);

            if (!$model) {
                return 'Hình ảnh';
            }

            // Special handling cho Product
            if ($model instanceof Product) {
                $order = $image->order ?? 0;
                if ($order === 0) {
                    return "{$model->name}";
                }
                return "{$model->name} hình {$order}";
            }

            // Generic handling cho model khác
            $nameField = $this->getModelNameField($model);
            $name = $model->$nameField ?? 'Hình ảnh';
            $order = $image->order ?? 0;
            
            if ($order === 0) {
                return $name;
            }
            return "{$name} hình {$order}";
        } catch (\Throwable $e) {
            \Log::warning('Failed to generate alt text', [
                'image_id' => $image->id,
                'model_type' => $image->model_type,
                'model_id' => $image->model_id,
                'error' => $e->getMessage(),
            ]);
            return 'Hình ảnh';
        }
    }

    /**
     * Lấy field name từ model
     */
    private function getModelNameField($model): string
    {
        if (method_exists($model, 'getNameAttribute')) {
            return 'name';
        }

        if (property_exists($model, 'name')) {
            return 'name';
        }

        if (property_exists($model, 'title')) {
            return 'title';
        }

        return 'id';
    }

    public function created(Image $image): void
    {
        $this->incrementCacheVersion();
    }

    public function updated(Image $image): void
    {
        $this->incrementCacheVersion();
    }

    public function deleted(Image $image): void
    {
        $this->incrementCacheVersion();
    }

    public function restored(Image $image): void
    {
        $this->incrementCacheVersion();
    }

    public function forceDeleted(Image $image): void
    {
        $this->incrementCacheVersion();
    }
}
