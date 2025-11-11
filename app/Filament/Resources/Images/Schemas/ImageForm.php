<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Models\Article;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Tệp hình ảnh')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Tải lên hình ảnh')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->image()
                            ->imageEditor()
                            ->maxFiles(1)
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('media/images')
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Get $get) {
                                $filename = 'img-' . Str::uuid() . '.webp';
                                $disk = 'public';
                                $path = 'media/images/' . $filename;

                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($file->getRealPath());

                                if ($image->width() > 1920) {
                                    $image->scale(width: 1920);
                                }

                                $webp = $image->toWebp(quality: 85);
                                Storage::disk($disk)->put($path, $webp);

                                return $path;
                            })
                            ->afterStateUpdated(function ($state, $set) {
                                if (!$state) {
                                    return;
                                }

                                try {
                                    $disk = 'public';
                                    $fullPath = Storage::disk($disk)->path($state);
                                    
                                    if (file_exists($fullPath)) {
                                        [$width, $height] = getimagesize($fullPath);
                                        $set('width', $width);
                                        $set('height', $height);
                                        $set('mime', 'image/webp');
                                        $set('disk', $disk);
                                    }
                                } catch (\Throwable $e) {
                                    // Observer sẽ handle metadata extraction
                                }
                            })
                            ->columnSpanFull()
                            ->helperText('Tải lên ảnh mới (tự động convert sang WebP, mặc định lưu vào local storage)'),
                    ]),
                Section::make('Thông tin')
                    ->columns(2)
                    ->schema([
                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('Gắn với')
                    ->description('Tùy chọn - Có thể để trống để tạo ảnh độc lập (logo, favicon, icon...)')
                    ->schema([
                        MorphToSelect::make('model')
                            ->label('Thuộc về')
                            ->types([
                                Type::make(Product::class)
                                    ->label('Sản phẩm')
                                    ->titleAttribute('name'),
                                Type::make(Article::class)
                                    ->label('Bài viết')
                                    ->titleAttribute('title'),
                            ])
                            ->required(false)
                            ->preload()
                            ->searchable(),
                    ]),
                
                // Hidden fields - sẽ được auto-fill bởi observer
                Hidden::make('disk')
                    ->default('public'),
                Hidden::make('width'),
                Hidden::make('height'),
                Hidden::make('mime'),
                
                Section::make('Thuộc tính bổ sung')
                    ->collapsed()
                    ->description('Thông tin mở rộng, không bắt buộc')
                    ->schema([
                        KeyValue::make('extra_attributes')
                            ->keyLabel('Tên trường')
                            ->valueLabel('Giá trị')
                            ->nullable()
                            ->reorderable()
                            ->addButtonLabel('Thêm thuộc tính')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
