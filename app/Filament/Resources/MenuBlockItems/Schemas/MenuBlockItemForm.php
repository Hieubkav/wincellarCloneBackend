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
                Section::make('Thông tin cơ bản')
                    ->description('Chọn 1 trong 2 mode: (1) Chọn Thuật ngữ từ taxonomy → auto label/href, hoặc (2) Nhập thủ công Label + Đường dẫn')
                    ->columns(2)
                    ->schema([
                        Select::make('menu_block_id')
                            ->label('Khối menu')
                            ->relationship('block', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('term_id')
                            ->label('Thuật ngữ (Mode 1: Auto)')
                            ->helperText('Chọn thuật ngữ từ catalog → tự động lấy tên và tạo link filter')
                            ->relationship('term', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('label')
                            ->label('Nhãn hiển thị (Mode 2: Thủ công)')
                            ->helperText('Để trống nếu dùng thuật ngữ ở trên')
                            ->maxLength(255),
                        TextInput::make('href')
                            ->label('Đường dẫn (Mode 2: Thủ công)')
                            ->helperText('VD: tel:+84938123456, mailto:abc@xyz.com, https://...')
                            ->maxLength(2048),
                        TextInput::make('badge')
                            ->label('Nhãn đặc biệt')
                            ->maxLength(50)
                            ->placeholder('VD: Mới, Hot, Sale'),
                        TextInput::make('order')
                            ->label('Thứ tự hiển thị')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->required()
                            ->default(true)
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),
                Section::make('Icon')
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
