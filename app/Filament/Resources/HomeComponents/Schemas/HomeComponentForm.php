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
                            ->datalist(self::typeSuggestions())
                            ->helperText('Mã khối dùng bởi frontend. Ví dụ: hero_banner, featured_products, article_list'),
                        Grid::make()
                            ->schema([
                                TextInput::make('order')
                                    ->label('Thứ tự hiển thị')
                                    ->helperText('Số nhỏ sẽ hiển thị trước trên trang chủ')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1),
                                Toggle::make('active')
                                    ->label('Đang hiển thị')
                                    ->helperText('Bật để hiển thị khối này')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        KeyValue::make('config')
                            ->label('Cấu hình hiển thị')
                            ->keyLabel('Tên trường')
                            ->valueLabel('Giá trị')
                            ->reorderable()
                            ->addButtonLabel('Thêm cấu hình')
                            ->nullable()
                            ->columnSpanFull()
                            ->helperText('Dữ liệu bổ sung cho frontend. Ví dụ: title=Sản phẩm nổi bật, limit=8, theme=dark'),
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
