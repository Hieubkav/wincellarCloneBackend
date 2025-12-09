<?php

namespace App\Filament\Resources\MenuBlockItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MenuBlockItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin Item')
                    ->columns(2)
                    ->schema([
                        Select::make('menu_block_id')
                            ->label('Thuộc cột')
                            ->relationship('block', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('label')
                            ->label('Nhãn hiển thị')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('VD: Vang đỏ, Pháp, Cabernet'),
                        TextInput::make('href')
                            ->label('Đường dẫn')
                            ->required()
                            ->maxLength(2048)
                            ->placeholder('VD: /filter?type=1&category=2')
                            ->helperText('Nhập link thủ công'),
                        TextInput::make('badge')
                            ->label('Badge')
                            ->maxLength(50)
                            ->placeholder('VD: HOT, NEW, SALE'),
                        Toggle::make('active')
                            ->label('Hiển thị')
                            ->required()
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('Icon (tùy chọn)')
                    ->collapsed()
                    ->schema([
                        FileUpload::make('icon_image')
                            ->label('Ảnh icon')
                            ->image()
                            ->disk('public')
                            ->directory('menu-items')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->maxFiles(1)
                            ->acceptedFileTypes(['image/*'])
                            ->saveUploadedFileUsing(function ($file) {
                                $filename = uniqid('menu_item_') . '.webp';
                                $path = 'menu-items/' . $filename;

                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($file->getRealPath());

                                if ($image->width() > 400) {
                                    $image->scale(width: 400);
                                }

                                $webp = $image->toWebp(quality: 85);
                                Storage::disk('public')->put($path, $webp);

                                return $path;
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
