<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductCategoryForm
{
    /**
     * Build the management form for product categories.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin nhóm sản phẩm')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên nhóm')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('active')
                            ->label('Đang hiển thị')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
