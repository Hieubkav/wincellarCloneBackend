<?php

namespace App\Filament\Resources\MenuBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                    ->label('Tiêu đề cột')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('VD: Theo loại, Theo quốc gia')
                    ->helperText('Tiêu đề hiển thị ở đầu cột trong mega menu'),
                Toggle::make('active')
                    ->label('Hiển thị')
                    ->default(true)
                    ->inline(false),
            ])
            ->columns(2);
    }
}
