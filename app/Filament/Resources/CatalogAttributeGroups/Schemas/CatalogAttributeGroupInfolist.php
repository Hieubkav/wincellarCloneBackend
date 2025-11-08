<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CatalogAttributeGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Code')
                                    ->badge(),
                                TextEntry::make('name')
                                    ->label('Name')
                                    ->weight('medium'),
                                TextEntry::make('filter_type')
                                    ->label('Filter type')
                                    ->formatStateUsing(fn (?string $state): ?string => $state ? ucfirst($state) : null)
                                    ->badge(),
                                TextEntry::make('terms_count')
                                    ->label('Terms')
                                    ->badge()
                                    ->state(fn ($record) => $record->terms()->count()),
                                IconEntry::make('is_filterable')
                                    ->label('Filterable')
                                    ->boolean(),
                                TextEntry::make('position')
                                    ->label('Order')
                                    ->numeric(),
                            ]),
                        Grid::make()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created at')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Updated at')
                                    ->dateTime(),
                            ])
                            ->columns(2),
                    ]),
                Section::make('Display configuration')
                    ->schema([
                        KeyValueEntry::make('display_config')
                            ->hidden(fn ($record) => blank($record->display_config ?? []))
                            ->keyLabel('Key')
                            ->valueLabel('Value'),
                    ]),
            ]);
    }
}
