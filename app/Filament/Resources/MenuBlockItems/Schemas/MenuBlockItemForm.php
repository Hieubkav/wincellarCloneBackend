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
                TextInput::make('menu_block_id')
                    ->required()
                    ->numeric(),
                Select::make('term_id')
                    ->relationship('term', 'name')
                    ->default(null),
                TextInput::make('label')
                    ->default(null),
                TextInput::make('href')
                    ->default(null),
                TextInput::make('badge')
                    ->default(null),
                Textarea::make('meta')
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
