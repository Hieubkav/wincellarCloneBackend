<?php

namespace App\Filament\Resources\MenuBlockItems\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MenuBlockItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('menu_block_id')
                    ->numeric(),
                TextEntry::make('term.name')
                    ->numeric(),
                TextEntry::make('label'),
                TextEntry::make('href'),
                TextEntry::make('badge'),
                TextEntry::make('order')
                    ->numeric(),
                IconEntry::make('active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
