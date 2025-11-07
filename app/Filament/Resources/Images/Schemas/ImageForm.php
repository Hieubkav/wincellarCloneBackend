<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Models\Article;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Schemas\Schema;

use Illuminate\Support\Str;
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
                            ->default(config('filesystems.default'))
                            ->helperText('Chọn nơi lưu file (thường là public)'),
                        FileUpload::make('file_path')
                            ->label('Tải lên hình ảnh')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->image()
                            ->visibility('public')
                            ->maxFiles(1)
                            ->maxSize(10240)
                            ->disk(fn (Get $get): string => $get('disk') ?? config('filesystems.default'))
                            ->directory('media/images')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                return 'img-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                            })
                            ->helperText('Tải lên JPG/PNG/WebP tối đa 10MB. File mới sẽ thay thế file cũ'),
                    ]),
                Section::make('Thông tin')
                    ->columns(2)
                    ->schema([
                        TextInput::make('alt')
                            ->label('Mô tả ảnh (Alt text)')
                            ->maxLength(255)
                            ->helperText('Mô tả ngắn cho SEO và người khiếm thị'),
                        TextInput::make('order')
                            ->label('Thứ tự hiển thị')
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Số 0 đánh dấu ảnh đại diện chính. Số khác theo thứ tự bộ sưu tập'),
                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->helperText('Bật để hiển thị ảnh này')
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('Gắn với')
                    ->schema([
                        MorphToSelect::make('model')
                            ->label('Thuộc về')
                            ->helperText('Chọn sản phẩm hoặc bài viết mà ảnh này thuộc về')
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
                            ->columnSpanFull()
                            ->helperText('Dữ liệu tùy chỉnh như chú thích, điểm tập trung...'),
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
