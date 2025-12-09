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
                TextEntry::make('block.title')
                    ->label('Thuộc cột'),
                TextEntry::make('label')
                    ->label('Nhãn'),
                TextEntry::make('href')
                    ->label('Đường dẫn'),
                TextEntry::make('badge')
                    ->label('Badge'),
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
