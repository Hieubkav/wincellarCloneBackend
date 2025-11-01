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
                    ->relationship('menu', 'title')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Select::make('attribute_group_id')
                    ->relationship('attributeGroup', 'name')
                    ->default(null),
                TextInput::make('max_terms')
                    ->numeric()
                    ->default(null),
                Textarea::make('config')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
