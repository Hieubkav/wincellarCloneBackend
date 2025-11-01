<?php

namespace App\Filament\Resources\ProductTypes\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Type details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name')
                                    ->weight('medium'),
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->badge(),
                                TextEntry::make('order')
                                    ->label('Display order')
                                    ->numeric(),
                                TextEntry::make('products_count')
                                    ->label('Attached products')
                                    ->state(fn ($record) => $record->products()->count())
                                    ->badge(),
                                IconEntry::make('active')
                                    ->label('Active')
                                    ->boolean(),
                            ]),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('—')
                            ->columnSpanFull()
                            ->hidden(fn ($record) => blank($record->description)),
                    ]),
                Section::make('Timestamps')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created at')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Last updated')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }
}
