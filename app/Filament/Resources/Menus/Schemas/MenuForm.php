<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin Menu')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Tiêu đề')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('VD: Rượu vang, Liên hệ'),
                        TextInput::make('href')
                            ->label('Đường dẫn')
                            ->required()
                            ->maxLength(2048)
                            ->placeholder('VD: /filter?type=1, /contact')
                            ->helperText('Nhập link thủ công'),
                        Select::make('type')
                            ->label('Kiểu menu')
                            ->options([
                                'standard' => 'Link đơn (không dropdown)',
                                'mega' => 'Mega menu (có nhiều cột)',
                            ])
                            ->required()
                            ->default('standard')
                            ->helperText('Mega menu sẽ hiển thị các Block con'),
                        Toggle::make('active')
                            ->label('Hiển thị')
                            ->required()
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
