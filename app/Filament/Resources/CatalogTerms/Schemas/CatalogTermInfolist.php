<?php

namespace App\Filament\Resources\CatalogTerms\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CatalogTermInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Term details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('group.name')
                                    ->label('Attribute group')
                                    ->badge(),
                                TextEntry::make('parent.name')
                                    ->label('Parent term')
                                    ->placeholder('Top level'),
                                TextEntry::make('name')
                                    ->label('Name')
                                    ->weight('medium'),
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->copyable(),
                                TextEntry::make('icon_type')
                                    ->label('Icon type')
                                    ->placeholder('—'),
                                TextEntry::make('icon_value')
                                    ->label('Icon value')
                                    ->placeholder('—'),
                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),
                                TextEntry::make('position')
                                    ->label('Display order')
                                    ->numeric(),
                                TextEntry::make('products_count')
                                    ->label('Attached products')
                                    ->state(fn ($record) => $record->products()->count())
                                    ->badge(),
                            ]),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('—')
                            ->columnSpanFull()
                            ->hidden(fn ($record) => blank($record->description)),
                    ]),
                Section::make('Metadata')
                    ->collapsed()
                    ->schema([
                        KeyValueEntry::make('metadata')
                            ->label('Metadata')
                            ->hidden(fn ($record) => blank($record->metadata ?? []))
                            ->columnSpanFull(),
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
