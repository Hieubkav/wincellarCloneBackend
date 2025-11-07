<?php

namespace App\Filament\Resources\MenuBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('menu_id')
                    ->label('Menu cha')
                    ->helperText('Chọn menu chứa khối này')
                    ->relationship('menu', 'title')
                    ->searchable()
                    ->required(),
                TextInput::make('title')
                    ->label('Tiêu đề khối')
                    ->helperText('Tên phần trong menu mở rộng. Ví dụ: "Theo xuất xứ", "Theo thương hiệu"')
                    ->required(),
                Select::make('attribute_group_id')
                    ->label('Nhóm thuộc tính')
                    ->helperText('Chọn nhóm để tự động hiển thị các thuật ngữ')
                    ->relationship('attributeGroup', 'name')
                    ->searchable()
                    ->default(null),
                TextInput::make('max_terms')
                    ->label('Giới hạn số lượng')
                    ->helperText('Số thuật ngữ tối đa hiển thị. Để trống = không giới hạn')
                    ->numeric()
                    ->minValue(1)
                    ->default(null),
                Textarea::make('config')
                    ->label('Cấu hình JSON')
                    ->helperText('Cấu hình bổ sung dạng JSON (dành cho dev)')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->label('Thứ tự hiển thị')
                    ->helperText('Số nhỏ sẽ hiển thị trước trong menu')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->label('Đang hiển thị')
                    ->helperText('Bật để hiển thị khối này')
                    ->required()
                    ->default(true),
            ]);
    }
}
