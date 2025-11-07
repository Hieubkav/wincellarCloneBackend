<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CatalogAttributeGroupForm
{
    /**
     * Compose the form used to manage catalog attribute groups.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin chung')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Mã nhóm')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Mã định danh duy nhất. Ví dụ: brand, country, region, grape'),
                        TextInput::make('name')
                            ->label('Tên hiển thị')
                            ->helperText('Ví dụ: Thương hiệu, Quốc gia, Vùng miền, Giống nho')
                            ->required()
                            ->maxLength(255),
                        Select::make('filter_type')
                            ->label('Kiểu bộ lọc')
                            ->required()
                            ->default('multi')
                            ->options([
                                'single' => 'Chọn đơn',
                                'multi' => 'Chọn nhiều',
                                'hierarchy' => 'Phân cấp',
                                'range' => 'Khoảng',
                            ])
                            ->helperText('Quyết định cách hiển thị bộ lọc trên website'),
                        Grid::make()
                            ->schema([
                                Toggle::make('is_filterable')
                                    ->label('Cho phép lọc')
                                    ->helperText('Bật để hiển thị trong bộ lọc')
                                    ->default(true)
                                    ->inline(false),
                                Toggle::make('is_primary')
                                    ->label('Nhóm chính')
                                    ->default(false)
                                    ->inline(false)
                                    ->helperText('Nhóm chính sẽ dùng cho breadcrumb và điều hướng quan trọng'),
                            ])
                            ->columns(2),
                        TextInput::make('position')
                            ->label('Thứ tự hiển thị')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Số nhỏ sẽ hiển thị trước'),
                    ]),
                Section::make('Cấu hình hiển thị')
                    ->collapsed()
                    ->description('Tùy chỉnh giao diện, không bắt buộc')
                    ->schema([
                        KeyValue::make('display_config')
                            ->keyLabel('Tên cấu hình')
                            ->valueLabel('Giá trị')
                            ->reorderable()
                            ->addButtonLabel('Thêm cấu hình')
                            ->nullable()
                            ->helperText('Cấu hình cho frontend như icon, màu sắc, template...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
