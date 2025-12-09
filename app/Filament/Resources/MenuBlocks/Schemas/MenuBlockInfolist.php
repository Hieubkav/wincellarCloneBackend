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
                    ->label('Menu cha'),
                TextEntry::make('title')
                    ->label('Tiêu đề cột'),
                TextEntry::make('order')
                    ->label('Thứ tự')
                    ->numeric(),
                IconEntry::make('active')
                    ->label('Hiển thị')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime(),
            ]);
    }
}
