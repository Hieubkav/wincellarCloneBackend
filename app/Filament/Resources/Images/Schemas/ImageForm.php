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
                Section::make('File')
                    ->columns(2)
                    ->schema([
                        Select::make('disk')
                            ->label('Storage disk')
                            ->required()
                            ->options(self::diskOptions())
                            ->default(config('filesystems.default'))
                            ->helperText('Choose where the file will be stored.'),
                        FileUpload::make('file_path')
                            ->label('File')
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
                            ->helperText('Upload JPG/PNG/WebP up to 10MB. A new upload will replace the existing file.'),
                    ]),
                Section::make('Meta')
                    ->columns(2)
                    ->schema([
                        TextInput::make('alt')
                            ->label('Alt text')
                            ->maxLength(255)
                            ->helperText('Short description used for accessibility and SEO.'),
                        TextInput::make('order')
                            ->label('Display order')
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->step(1)
                            ->helperText('0 marks the cover image for a gallery.'),
                        Toggle::make('active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('Attached to')
                    ->schema([
                        MorphToSelect::make('model')
                            ->label('Owner')
                            ->types([
                                Type::make(Product::class)
                                    ->label('Product')
                                    ->titleAttribute('name'),
                                Type::make(Article::class)
                                    ->label('Article')
                                    ->titleAttribute('title'),
                            ])
                            ->required()
                            ->preload()
                            ->searchable()
                            ->helperText('Link the image to the record it belongs to.'),
                    ]),
                Section::make('Derived data')
                    ->columns(3)
                    ->schema([
                        TextInput::make('width')
                            ->label('Width (px)')
                            ->numeric()
                            ->dehydrated(false)
                            ->disabled(),
                        TextInput::make('height')
                            ->label('Height (px)')
                            ->numeric()
                            ->dehydrated(false)
                            ->disabled(),
                        TextInput::make('mime')
                            ->label('MIME type')
                            ->maxLength(191)
                            ->dehydrated(false)
                            ->disabled(),
                    ]),
                Section::make('Extra attributes')
                    ->collapsed()
                    ->schema([
                        KeyValue::make('extra_attributes')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->nullable()
                            ->reorderable()
                            ->addButtonLabel('Add attribute')
                            ->columnSpanFull()
                            ->helperText('Optional metadata (caption, focal point, etc.).'),
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
