<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Schemas;

use App\Models\CatalogAttributeGroup;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CatalogAttributeGroupForm
{
    /**
     * Form tạo/sửa nhóm thuộc tính.
     * Ẩn mã nhóm, thứ tự, icon; mã & thứ tự sẽ tự sinh.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin chung')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên hiển thị')
                            ->helperText('Ví dụ: Thương hiệu, Quốc gia, Vùng miền, Giống nho')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true) // tránh Livewire gửi request liên tục khi gõ
                            
                            ->afterStateUpdated(fn ($state, callable $set) => $set('code', Str::slug((string) $state, '_'))),

                        Select::make('filter_type')
                            ->label('Kiểu bộ lọc')
                            ->required()
                            ->default('chon_nhieu')
                            ->live()
                            ->options([
                                'chon_don' => 'Chọn đơn',
                                'chon_nhieu' => 'Chọn nhiều',
                                'nhap_tay' => 'Nhập tay',
                            ])
                            ->helperText('Quyết định cách hiển thị bộ lọc trên website'),

                        Select::make('input_type')
                            ->label('Kiểu nhập')
                            ->options([
                                'text' => 'Text',
                                'number' => 'Số',
                            ])
                            ->default('text')
                            ->required(fn (Get $get) => $get('filter_type') === 'nhap_tay')
                            ->hidden(fn (Get $get) => $get('filter_type') !== 'nhap_tay')
                            ->helperText('Chọn nhập số hoặc text khi kiểu lộc là Nhập tay'),

                        Toggle::make('is_filterable')
                            ->label('Cho phép lọc')
                            ->helperText('Bật để hiển thị trong bộ lọc')
                            ->default(true)
                            ->inline(false),

                        // Ẩn nhưng vẫn gửi về server
                        Hidden::make('code')
                            ->dehydrated(true)
                            ->required()
                            ->default(fn (Get $get) => Str::slug((string) ($get('name') ?? ''), '_'))
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(function ($state, Get $get) {
                                $value = $state ?: Str::slug((string) ($get('name') ?? ''), '_');
                                return $value ?: Str::random(8);
                            }),

                        Hidden::make('position')
                            ->dehydrated(true)
                            ->default(fn () => (int) (CatalogAttributeGroup::max('position') ?? 0) + 1),

                        Hidden::make('icon_path')
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
