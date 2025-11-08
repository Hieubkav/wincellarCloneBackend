<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\KeyValue;
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
                Section::make('Thông tin cơ bản')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Tiêu đề menu')
                            ->maxLength(255),
                        Select::make('term_id')
                            ->label('Thuật ngữ liên kết')
                            ->relationship('term', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->label('Kiểu menu')
                            ->options([
                                'standard' => 'Menu thường',
                                'mega' => 'Menu mở rộng (Mega)',
                            ])
                            ->required()
                            ->default('standard'),
                        TextInput::make('href')
                            ->label('Đường dẫn')
                            ->maxLength(2048),
                        TextInput::make('order')
                            ->label('Thứ tự hiển thị')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->required()
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('Cấu hình bổ sung')
                    ->collapsed()
                    ->schema([
                        KeyValue::make('config')
                            ->label('Cấu hình')
                            ->keyLabel('Tên trường')
                            ->valueLabel('Giá trị')
                            ->nullable()
                            ->reorderable()
                            ->addButtonLabel('Thêm cấu hình')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
