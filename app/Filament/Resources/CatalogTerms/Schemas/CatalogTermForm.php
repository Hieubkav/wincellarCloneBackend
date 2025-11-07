<?php

namespace App\Filament\Resources\CatalogTerms\Schemas;

use App\Models\CatalogAttributeGroup;
use App\Models\CatalogTerm;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                            ->helperText('Chọn nhóm như Thương hiệu, Xuất xứ, Giống nho...')
                            ->required()
                            ->options(fn (): array => CatalogAttributeGroup::query()
                                ->orderBy('position')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('parent_id')
                            ->label('Thuộc tính cha')
                            ->placeholder('Cấp cao nhất')
                            ->helperText('Để trống nếu đây là mục cấp cao nhất')
                            ->options(function (Get $get, ?CatalogTerm $record): array {
                                $groupId = $get('group_id');

                                if (!$groupId) {
                                    return [];
                                }

                                return CatalogTerm::query()
                                    ->where('group_id', $groupId)
                                    ->when(
                                        $record,
                                        fn ($query) => $query->where('id', '!=', $record->getKey())
                                    )
                                    ->orderBy('position')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('Tên thuộc tính')
                            ->helperText('Ví dụ: Pháp, Ý, Bordeaux, Champagne...')
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
                            ->rule('alpha_dash')
                            ->unique(ignoreRecord: true)
                            ->helperText('Đường dẫn URL. Chỉ dùng chữ thường, số, gạch ngang, gạch dưới'),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Mô tả chi tiết, có thể hiển thị trên trang lọc sản phẩm'),
                        Grid::make()
                            ->schema([
                                TextInput::make('icon_type')
                                    ->label('Loại biểu tượng')
                                    ->helperText('Ví dụ: image, emoji, icon...')
                                    ->maxLength(50),
                                TextInput::make('icon_value')
                                    ->label('Giá trị biểu tượng')
                                    ->helperText('Đường dẫn ảnh hoặc mã biểu tượng')
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                        Grid::make()
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Đang hiển thị')
                                    ->helperText('Bật để hiển thị trên website')
                                    ->default(true)
                                    ->inline(false),
                                TextInput::make('position')
                                    ->label('Thứ tự hiển thị')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('Số nhỏ sẽ hiển thị trước trong cùng nhóm'),
                            ])
                            ->columns(2),
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
