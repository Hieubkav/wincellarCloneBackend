<?php

namespace App\Filament\Resources\HomeComponents\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class HomeComponentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Component details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Component')
                                    ->badge(),
                                TextEntry::make('order')
                                    ->label('Order')
                                    ->numeric(),
                                IconEntry::make('active')
                                    ->label('Active')
                                    ->boolean(),
                            ]),
                        TextEntry::make('config')
                            ->label('Configuration JSON')
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state) => empty($state) ? '—' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->copyable()
                            ->copyMessage('Configuration copied'),
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
