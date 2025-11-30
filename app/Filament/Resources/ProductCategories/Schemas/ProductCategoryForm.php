<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Models\ProductType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
                        Select::make('type_id')
                            ->label('Phân mục')
                            ->options(fn () => ProductType::active()->orderBy('order')->orderBy('id')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText('Gán danh mục vào phân mục để lọc form sản phẩm và bộ lọc FE.'),
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
