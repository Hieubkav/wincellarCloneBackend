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
            ->schema([
                Select::make('menu_id')
                    ->label('Menu cha')
                    ->relationship('menu', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->label('Tiêu đề khối')
                    ->required()
                    ->maxLength(255),
                Select::make('attribute_group_id')
                    ->label('Nhóm thuộc tính')
                    ->relationship('attributeGroup', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('max_terms')
                    ->label('Giới hạn số lượng')
                    ->numeric()
                    ->minValue(1),
                Textarea::make('config')
                    ->label('Cấu hình JSON')
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->label('Đang hiển thị')
                    ->default(true)
                    ->inline(false),
            ])
            ->columns(2);
    }
}
