<?php

namespace App\Filament\Resources\ProductTypes\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductTypeForm
{
    /**
     * Build the management form for product types.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin phân loại')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên phân loại')
                            ->helperText('Ví dụ: Vang đỏ, Vang trắng, Bia chai, Bia lon, Xúc xích...')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, Get $get): void {
                                if (blank($get('slug')) && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Đường dẫn')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rule('alpha_dash')
                            ->helperText('Đường dẫn URL. Chỉ dùng chữ thường, số, gạch ngang, gạch dưới'),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Mô tả chi tiết loại sản phẩm'),
                        Grid::make()
                            ->schema([
                                TextInput::make('order')
                                    ->label('Thứ tự hiển thị')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('Số nhỏ sẽ hiển thị trước'),
                                Toggle::make('active')
                                    ->label('Đang hiển thị')
                                    ->helperText('Bật để hiển thị loại này')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
