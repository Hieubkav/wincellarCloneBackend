<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->default(null),
                Select::make('term_id')
                    ->relationship('term', 'name')
                    ->default(null),
                TextInput::make('type')
                    ->required()
                    ->default('standard'),
                TextInput::make('href')
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
