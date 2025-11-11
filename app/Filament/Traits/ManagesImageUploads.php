<?php

namespace App\Filament\Traits;

use App\Models\Image;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ManagesImageUploads
{
    /**
     * Get default disk for all image uploads.
     * 
     * IMPORTANT: Always returns 'public' for web accessibility.
     * This is intentionally hardcoded, not configurable per environment.
     * 
     * Reason: All images must be accessible via HTTP URLs:
     * - /storage/articles/article_xyz.webp
     * - /storage/products/product_abc.webp
     * - /storage/branding/logo.webp
     * 
     * Using 'public' disk ensures files are in storage/app/public/
     * and accessible without authentication via symlink.
     * 
     * If you need different storage for other use cases,
     * create separate trait or override this method in subclass.
     */
    protected function getDefaultDisk(): string
    {
        return 'public';  // ← Intentionally hardcoded for web access
    }

    /**
     * Lấy directory upload dựa trên context
     */
    protected function getUploadDirectory(): string
    {
        // Override trong subclass nếu cần
        return 'media/images';
    }

    /**
     * Lấy image quality cho WebP conversion
     */
    protected function getImageQuality(): int
    {
        return 85;
    }

    /**
     * Lấy max width cho resize
     */
    protected function getMaxImageWidth(): int
    {
        return 1200;
    }

    /**
     * Common form schema cho image upload
     */
    protected function getImageUploadFormSchema(): array
    {
        return [
            Tabs::make('ImageUpload')
                ->tabs([
                    Tabs\Tab::make('Tải lên mới')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->schema([
                            FileUpload::make('file_path')
                                ->label('Chọn file ảnh')
                                ->required(fn(string $operation): bool => $operation === 'create')
                                ->image()
                                ->disk($this->getDefaultDisk())
                                ->directory($this->getUploadDirectory())
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(10240)
                                ->acceptedFileTypes(['image/*'])
                                ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                                    return $this->handleImageUpload($file);
                                })
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    $this->extractImageMetadata($state, $set, $get);
                                })
                                ->columnSpanFull()
                                ->helperText('Tải lên ảnh mới (tự động convert sang WebP)'),
                        ]),
                ])
                ->contained(false)
                ->columnSpanFull(),

            Toggle::make('active')
                ->label('Hiển thị')
                ->default(true)
                ->inline(false),
        ];
    }

    /**
     * Xử lý upload ảnh: convert sang WebP và resize
     */
    protected function handleImageUpload(TemporaryUploadedFile $file): string
    {
        $prefix = $this->getFilenamePrefix();
        $filename = uniqid($prefix . '_') . '.webp';
        $path = $this->getUploadDirectory() . '/' . $filename;

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        $maxWidth = $this->getMaxImageWidth();
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $webp = $image->toWebp(quality: $this->getImageQuality());
        Storage::disk($this->getDefaultDisk())->put($path, $webp);

        return $path;
    }

    /**
     * Lấy prefix cho filename (override trong subclass)
     */
    protected function getFilenamePrefix(): string
    {
        return 'img';
    }

    /**
     * Extract metadata từ file ảnh đã upload
     */
    protected function extractImageMetadata($state, $set, $get): void
    {
        if (!$state) {
            return;
        }

        try {
            $disk = $this->getDefaultDisk();
            $fullPath = Storage::disk($disk)->path($state);
            
            if (file_exists($fullPath)) {
                [$width, $height] = getimagesize($fullPath);
                $set('width', $width);
                $set('height', $height);
                $set('mime', 'image/webp');
                $set('disk', $disk);
            }
        } catch (\Throwable $e) {
            // Silent fail - Observer sẽ handle metadata extraction
        }
    }

    /**
     * Common table columns cho image listing
     */
    protected function getImageTableColumns(): array
    {
        return [
            ImageColumn::make('file_path')
                ->label('Ảnh')
                ->disk($this->getDefaultDisk())
                ->width(80)
                ->height(80)
                ->defaultImageUrl('/images/placeholder.png'),

            TextColumn::make('width')
                ->label('Kích thước')
                ->formatStateUsing(fn($record) => $record->width && $record->height ? "{$record->width}x{$record->height}" : '-')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('active')
                ->label('Trạng thái')
                ->badge()
                ->formatStateUsing(fn($state) => $state ? 'Hiển thị' : 'Ẩn')
                ->color(fn($state) => $state ? 'success' : 'gray'),

            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Build form cho "Chọn từ thư viện" action
     */
    protected function buildLibrarySelectionForm($livewire): array
    {
        $owner = $livewire->getOwnerRecord();
        
        $existingImagePaths = $owner->images()
            ->whereNull('deleted_at')
            ->pluck('file_path')
            ->toArray();

        $images = Image::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $options = $images->mapWithKeys(function ($image) {
            $filename = basename($image->file_path);
            $imageUrl = $image->url ?? '/images/placeholder.png';
            
            $html = '<div style="display: flex; align-items: center; gap: 8px;">';
            $html .= '<img src="' . e($imageUrl) . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" />';
            $html .= '<span>' . e($filename) . '</span>';
            $html .= '</div>';
            
            return [$image->id => $html];
        })->toArray();
        
        $defaultSelected = $images
            ->filter(fn($img) => in_array($img->file_path, $existingImagePaths))
            ->pluck('id')
            ->toArray();

        return [
            \Filament\Forms\Components\CheckboxList::make('image_ids')
                ->label('Chọn ảnh')
                ->options($options)
                ->columns(3)
                ->gridDirection(\Filament\Support\Enums\GridDirection::Column)
                ->searchable()
                ->bulkToggleable()
                ->allowHtml()
                ->default($defaultSelected),
        ];
    }

    /**
     * Handle action "Chọn từ thư viện"
     */
    protected function handleLibrarySelection(array $data, $livewire): void
    {
        $owner = $livewire->getOwnerRecord();
        $selectedImageIds = $data['image_ids'] ?? [];

        if (empty($selectedImageIds)) {
            return;
        }

        $existingImagePaths = $owner->images()
            ->whereNull('deleted_at')
            ->pluck('file_path')
            ->toArray();

        $addedCount = 0;
        $skippedCount = 0;

        foreach ($selectedImageIds as $imageId) {
            $image = Image::find($imageId);
            if (!$image) {
                continue;
            }

            if (in_array($image->file_path, $existingImagePaths)) {
                $skippedCount++;
                continue;
            }

            $nextOrder = 0;
            while ($owner->images()->where('order', $nextOrder)->exists()) {
                $nextOrder++;
            }

            $owner->images()->create([
                'file_path' => $image->file_path,
                'disk' => $image->disk ?? $this->getDefaultDisk(),
                'alt' => $image->alt,
                'width' => $image->width,
                'height' => $image->height,
                'mime' => $image->mime,
                'order' => $nextOrder,
                'active' => true,
            ]);
            
            $addedCount++;
        }

        $message = "Đã thêm {$addedCount} ảnh mới";
        if ($skippedCount > 0) {
            $message .= ", bỏ qua {$skippedCount} ảnh đã có";
        }

        \Filament\Notifications\Notification::make()
            ->title('Hoàn tất')
            ->success()
            ->body($message)
            ->send();
    }
}
