<?php

namespace App\Filament\Resources\Articles\ArticleResource\RelationManagers;

use App\Models\Image;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Hình ảnh bài viết';

    protected static ?string $recordTitleAttribute = 'alt';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Tabs::make('ImageUpload')
                    ->tabs([
                        Tabs\Tab::make('Tải lên mới')
                            ->icon('heroicon-o-arrow-up-tray')
                            ->schema([
                                FileUpload::make('file_path')
                                    ->label('Chọn file ảnh')
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->image()
                                    ->disk('public')
                                    ->directory('articles')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->maxSize(10240)
                                    ->acceptedFileTypes(['image/*'])
                                    ->saveUploadedFileUsing(function ($file) {
                                        $filename = uniqid('article_') . '.webp';
                                        $path = 'articles/' . $filename;

                                        $manager = new ImageManager(new Driver());
                                        $image = $manager->read($file->getRealPath());

                                        if ($image->width() > 1200) {
                                            $image->scale(width: 1200);
                                        }

                                        $webp = $image->toWebp(quality: 85);
                                        Storage::disk('public')->put($path, $webp);

                                        return $path;
                                    })
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (!$state) {
                                            return;
                                        }

                                        try {
                                            $disk = $get('disk') ?? 'public';
                                            $fullPath = Storage::disk($disk)->path($state);
                                            
                                            if (file_exists($fullPath)) {
                                                [$width, $height] = getimagesize($fullPath);
                                                $set('width', $width);
                                                $set('height', $height);
                                                $set('mime', 'image/webp');
                                            }
                                        } catch (\Throwable $e) {
                                            // Ignore errors
                                        }
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_path')
                    ->label('Ảnh')
                    ->disk('public')
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
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->headerActions([
                CreateAction::make()
                    ->label('Tải lên mới')
                    ->icon('heroicon-o-arrow-up-tray'),
Action::make('selectFromLibrary')
                    ->label('Chọn từ thư viện')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->modalHeading('Chọn ảnh từ thư viện')
                    ->modalDescription('Chọn ảnh có sẵn trong hệ thống để thêm vào bài viết')
                    ->modalSubmitActionLabel('Thêm ảnh đã chọn')
                    ->modalWidth('7xl')
                    ->form(function (RelationManager $livewire) {
                        $article = $livewire->getOwnerRecord();
                        
                        // Get existing image file paths used by this article
                        $existingImagePaths = $article->images()
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
                            
                            // Use HTML for label with image preview
                            $html = '<div style="display: flex; align-items: center; gap: 8px;">';
                            $html .= '<img src="' . e($imageUrl) . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" />';
                            $html .= '<span>' . e($filename) . '</span>';
                            $html .= '</div>';
                            
                            return [$image->id => $html];
                        })->toArray();
                        
                        // Find IDs of images with matching file paths
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
                    })
                    ->action(function (array $data, RelationManager $livewire): void {
                        $article = $livewire->getOwnerRecord();
                        $selectedImageIds = $data['image_ids'] ?? [];

                        if (empty($selectedImageIds)) {
                            return;
                        }

                        // Get existing image file paths
                        $existingImagePaths = $article->images()
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

                            // Skip if already exists
                            if (in_array($image->file_path, $existingImagePaths)) {
                                $skippedCount++;
                                continue;
                            }

                            // Find next available order that doesn't exist
                            $nextOrder = 0;
                            while ($article->images()->where('order', $nextOrder)->exists()) {
                                $nextOrder++;
                            }

                            // Create a copy of the image for this article
                            $article->images()->create([
                                'file_path' => $image->file_path,
                                'disk' => $image->disk,
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
                    }),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50]);
    }
}
