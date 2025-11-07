<?php

namespace App\Filament\Resources\MenuBlockItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuBlockItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('menu_block_id')
                    ->label('Khối menu')
                    ->helperText('Chọn khối menu chứa mục này')
                    ->relationship('menuBlock', 'title')
                    ->searchable()
                    ->required(),
                Select::make('term_id')
                    ->label('Thuật ngữ')
                    ->helperText('Chọn thuật ngữ để tự động lấy tên và link')
                    ->relationship('term', 'name')
                    ->searchable()
                    ->default(null),
                TextInput::make('label')
                    ->label('Nhãn hiển thị')
                    ->helperText('Tên hiển thị. Để trống nếu dùng từ thuật ngữ')
                    ->default(null),
                TextInput::make('href')
                    ->label('Đường dẫn')
                    ->helperText('Link tùy chỉnh (nếu không dùng thuật ngữ)')
                    ->default(null),
                TextInput::make('badge')
                    ->label('Nhãn đặc biệt')
                    ->helperText('Hiển thị nhãn như "Mới", "Hot". Để trống nếu không cần')
                    ->default(null),
                Textarea::make('meta')
                    ->label('Dữ liệu bổ sung')
                    ->helperText('Thông tin thêm dạng JSON (dành cho dev)')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->label('Thứ tự hiển thị')
                    ->helperText('Số nhỏ sẽ hiển thị trước')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->label('Đang hiển thị')
                    ->helperText('Bật để hiển thị mục này')
                    ->required()
                    ->default(true),
            ]);
    }
}
