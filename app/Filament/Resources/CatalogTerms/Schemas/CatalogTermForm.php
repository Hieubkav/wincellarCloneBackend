<?php

namespace App\Filament\Resources\CatalogTerms\Schemas;

use App\Models\CatalogAttributeGroup;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CatalogTermForm
{
    /**
     * Configure the catalog term management form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin thuộc tính')
                    ->columns(2)
                    ->schema([
                        Select::make('group_id')
                            ->label('Nhóm thuộc tính')
                            ->required()
                            ->options(fn (): array => CatalogAttributeGroup::query()
                                ->whereIn('filter_type', ['chon_don', 'chon_nhieu'])
                                ->orderBy('position')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->label('Tên thuộc tính')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(3)
                            ->columnSpanFull(),
                        Grid::make()
                            ->schema([
                                TextInput::make('icon_type')
                                    ->label('Loại biểu tượng')
                                    ->maxLength(50),
                                TextInput::make('icon_value')
                                    ->label('Giá trị biểu tượng')
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                        Toggle::make('is_active')
                            ->label('Đang hiển thị')
                            ->default(true)
                            ->inline(false),
                    ]),
                Section::make('Dữ liệu bổ sung')
                    ->collapsed()
                    ->description('Thông tin mở rộng, không bắt buộc')
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Dữ liệu tùy chỉnh')
                            ->keyLabel('Tên trường')
                            ->valueLabel('Giá trị')
                            ->reorderable()
                            ->addButtonLabel('Thêm thông tin')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
