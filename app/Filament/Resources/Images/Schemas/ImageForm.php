<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Models\Article;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
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
                    ->columns(2)
                    ->schema([
                        Select::make('disk')
                            ->label('Nơi lưu trữ')
                            ->required()
                            ->options(self::diskOptions())
                            ->default(config('filesystems.default')),
                        FileUpload::make('file_path')
                            ->label('Tải lên hình ảnh')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->image()
                            ->imageEditor()
                            ->maxFiles(1)
                            ->maxSize(10240)
                            ->disk(fn (Get $get): string => $get('disk') ?? config('filesystems.default'))
                            ->directory('media/images')
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Get $get) {
                                $filename = 'img-' . Str::uuid() . '.webp';
                                $disk = $get('disk') ?? config('filesystems.default');
                                $path = 'media/images/' . $filename;

                                // Convert to WebP with 85% quality
                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($file->getRealPath());

                                // Resize if too large
                                if ($image->width() > 1920) {
                                    $image->scale(width: 1920);
                                }

                                $webp = $image->toWebp(quality: 85);
                                Storage::disk($disk)->put($path, $webp);

                                return $path;
                            }),
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
                            ->required()
                            ->preload()
                            ->searchable(),
                    ]),
                Section::make('Chi tiết kỹ thuật')
                    ->description('Hệ thống tự động nhận diện')
                    ->collapsed()
                    ->columns(3)
                    ->schema([
                        TextInput::make('width')
                            ->label('Chiều rộng (px)')
                            ->numeric()
                            ->dehydrated(false)
                            ->disabled(),
                        TextInput::make('height')
                            ->label('Chiều cao (px)')
                            ->numeric()
                            ->dehydrated(false)
                            ->disabled(),
                        TextInput::make('mime')
                            ->label('Định dạng')
                            ->maxLength(191)
                            ->dehydrated(false)
                            ->disabled(),
                        TextInput::make('alt')
                            ->label('Mô tả ảnh (Alt text)')
                            ->maxLength(255)
                            ->dehydrated(false)
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('order')
                            ->label('Thứ tự hiển thị')
                            ->numeric()
                            ->dehydrated(false)
                            ->disabled(),
                    ]),
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

    protected static function diskOptions(): array
    {
        return collect(config('filesystems.disks', []))
            ->keys()
            ->mapWithKeys(fn (string $disk): array => [$disk => $disk])
            ->all();
    }
}
