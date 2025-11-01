<?php

namespace App\Filament\Resources\Images\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ImageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Preview')
                    ->schema([
                        ImageEntry::make('url')
                            ->label('Image')
                            ->square()
                            ->hidden(fn ($record) => blank($record->url)),
                    ]),
                Section::make('Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('file_path')
                                    ->label('File path')
                                    ->copyable()
                                    ->columnSpan(2),
                                TextEntry::make('disk')
                                    ->label('Disk')
                                    ->badge(),
                                TextEntry::make('order')
                                    ->label('Order')
                                    ->numeric(),
                                IconEntry::make('active')
                                    ->label('Active')
                                    ->boolean(),
                                TextEntry::make('width')
                                    ->label('Width (px)')
                                    ->numeric(),
                                TextEntry::make('height')
                                    ->label('Height (px)')
                                    ->numeric(),
                                TextEntry::make('mime')
                                    ->label('MIME type'),
                            ]),
                        TextEntry::make('alt')
                            ->label('Alt text')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('model_type')
                            ->label('Owner type')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? class_basename($state) : null)
                            ->badge(),
                        TextEntry::make('model_id')
                            ->label('Owner ID')
                            ->numeric(),
                    ]),
                Section::make('Extra attributes')
                    ->collapsed()
                    ->schema([
                        KeyValueEntry::make('extra_attributes')
                            ->label('Attributes')
                            ->hidden(fn ($record) => blank($record->extra_attributes ?? []))
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
                                TextEntry::make('deleted_at')
                                    ->label('Deleted at')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }
}
