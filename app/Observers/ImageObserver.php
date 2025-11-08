<?php

namespace App\Observers;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ImageObserver
{
    /**
     * Handle the Image "creating" event.
     * Tự động set order và alt text cho ảnh mới
     */
    public function creating(Image $image): void
    {
        if ($image->order === null) {
            $image->order = $this->getNextOrder($image);
        }

        if (empty($image->alt)) {
            $image->alt = $this->generateAltText($image);
        }
    }

    /**
     * Handle the Image "updating" event.
     * Xóa file ảnh cũ khi upload file mới
     */
    public function updating(Image $image): void
    {
        if ($image->isDirty('file_path')) {
            $oldFilePath = $image->getOriginal('file_path');
            $oldDisk = $image->getOriginal('disk') ?? 'public';

            if ($oldFilePath && Storage::disk($oldDisk)->exists($oldFilePath)) {
                Storage::disk($oldDisk)->delete($oldFilePath);
            }
        }

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
     * Tính order tiếp theo cho ảnh mới
     */
    private function getNextOrder(Image $image): int
    {
        if (!$image->model_type || !$image->model_id) {
            return 1;
        }

        $maxOrder = Image::query()
            ->where('model_type', $image->model_type)
            ->where('model_id', $image->model_id)
            ->max('order');

        return $maxOrder !== null ? $maxOrder + 1 : 0;
    }

    /**
     * Tự động generate alt text từ product name + order
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

            if ($model instanceof Product) {
                $order = $image->order ?? 0;
                if ($order === 0) {
                    return "{$model->name}";
                }
                return "{$model->name} hình {$order}";
            }

            $nameField = $this->getModelNameField($model);
            return $model->$nameField ?? 'Hình ảnh';
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
}
