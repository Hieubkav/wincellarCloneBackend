<?php

namespace App\Filament\Resources\MenuBlockItems\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuBlockItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin cơ bản')
                    ->columns(2)
                    ->schema([
                        Select::make('menu_block_id')
                            ->label('Khối menu')
                            ->relationship('block', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('term_id')
                            ->label('Thuật ngữ')
                            ->relationship('term', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('label')
                            ->label('Nhãn hiển thị')
                            ->maxLength(255),
                        TextInput::make('href')
                            ->label('Đường dẫn')
                            ->maxLength(2048),
                        TextInput::make('badge')
                            ->label('Nhãn đặc biệt')
                            ->maxLength(50)
                            ->placeholder('VD: Mới, Hot, Sale'),
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
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),
                Section::make('Dữ liệu bổ sung')
                    ->collapsed()
                    ->schema([
                        KeyValue::make('meta')
                            ->label('Metadata')
                            ->keyLabel('Tên trường')
                            ->valueLabel('Giá trị')
                            ->nullable()
                            ->reorderable()
                            ->addButtonLabel('Thêm metadata')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
