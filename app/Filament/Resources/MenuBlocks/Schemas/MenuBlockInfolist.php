<?php

namespace App\Filament\Resources\MenuBlocks\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MenuBlockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('menu.title')
                    ->numeric(),
                TextEntry::make('title'),
                TextEntry::make('attributeGroup.name')
                    ->numeric(),
                TextEntry::make('max_terms')
                    ->numeric(),
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
