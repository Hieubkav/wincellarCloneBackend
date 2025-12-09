<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MenuInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Tiêu đề'),
                TextEntry::make('href')
                    ->label('Đường dẫn'),
                TextEntry::make('type')
                    ->label('Kiểu'),
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
