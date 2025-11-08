<?php

namespace App\Filament\Resources\HomeComponents\Schemas;

use App\Models\HomeComponent;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HomeComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Cấu hình khối giao diện')
                    ->description('Các khối này sẽ hiển thị trên trang chủ')
                    ->columns(2)
                    ->schema([
                        TextInput::make('type')
                            ->label('Loại khối')
                            ->required()
                            ->maxLength(120)
                            ->datalist(self::typeSuggestions()),
                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->default(true)
                            ->inline(false),
                        KeyValue::make('config')
                            ->label('Cấu hình hiển thị')
                            ->keyLabel('Tên trường')
                            ->valueLabel('Giá trị')
                            ->reorderable()
                            ->addButtonLabel('Thêm cấu hình')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return list<string>
     */
    protected static function typeSuggestions(): array
    {
        return HomeComponent::query()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort()
            ->values()
            ->all();
    }
}
