<?php

namespace App\Filament\Resources\Products;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\CatalogAttributeGroup;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Malzariey\FilamentLexicalEditor\LexicalEditor;

class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

    protected static ?array $attributeFieldKeys = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'Sản phẩm';

    protected static ?string $navigationLabel = 'Sản phẩm';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Sản phẩm';

    protected static ?string $pluralModelLabel = 'Các sản phẩm';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Thông tin chính')
                    ->columns(2)
                    ->schema([
                        Select::make('type_id')
                            ->label('Phân mục sản phẩm')
                            ->options(fn () => ProductType::active()->orderBy('order')->orderBy('id')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('categories', []);
                                static::clearAttributeFieldStates($set);
                            }),

                        Select::make('categories')
                            ->label('Danh mục')
                            ->relationship('categories', 'name')
                            ->options(function (callable $get) {
                                $typeId = $get('type_id');

                                return ProductCategory::query()
                                    ->when($typeId, fn ($query) => $query->where(function ($q) use ($typeId) {
                                        $q->where('type_id', $typeId)
                                            ->orWhereNull('type_id');
                                    }))
                                    ->where('active', true)
                                    ->orderBy('order')
                                    ->orderBy('id')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->multiple()
                            ->preload()
                            ->live()
                            ->disabled(fn (callable $get) => !$get('type_id'))
                            ->helperText('Chọn phân mục trước; danh mục sẽ lọc theo phân mục hoặc chưa gán phân mục.'),

                        TextInput::make('name')
                            ->label('Tên sản phẩm')
                            ->required()
                            ->maxLength(255)
                            ->copyable()
                            ->columnSpanFull(),

                        LexicalEditor::make('description')
                            ->label('Mô tả')
                            ->columnSpanFull(),
                    ]),

                Section::make('Giá & Thông số')
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Giá bán')
                                    ->type('text')
                                    ->prefix('₫')
                                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((int) $state, 0, '.', ',') : null)
                                    ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                    ->stripCharacters([',', '.'])
                                    ->numeric()
                                    ->minValue(0)
                                    ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? null : (int) $state)
                                    ->placeholder('Nhập giá bán'),

                                TextInput::make('original_price')
                                    ->label('Giá gốc')
                                    ->type('text')
                                    ->prefix('₫')
                                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((int) $state, 0, '.', ',') : null)
                                    ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                    ->stripCharacters([',', '.'])
                                    ->numeric()
                                    ->minValue(0)
                                    ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? null : (int) $state)
                                    ->placeholder('Nhập giá gốc'),

                                Toggle::make('active')
                                    ->label('Hoạt động')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Hinh anh')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('product_images')
                            ->label('Anh san pham (tai len moi)')
                            ->image()
                            ->multiple()
                            ->hidden(fn (?string $operation): bool => $operation === 'edit')
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->maxFiles(10)
                            ->imageEditor()
                            ->helperText('Co the bo trong khi tao; tai len toi da 10 anh neu can.'),
                    ]),

                Section::make('Thuộc tính')
                    ->columns(3)
                    ->schema(fn (Get $get) => static::getAttributeFields($get('type_id'))),
            ]);
    }

    /**
     * @param int|string|null $typeId
     * @return array<int, \Filament\Forms\Components\Component>
     */
    protected static function getAttributeFields(int|string|null $typeId): array
    {
        // Cast state to int because Filament Select usually returns string.
        $typeId = ($typeId !== null && $typeId !== '') ? (int) $typeId : null;

        $groups = static::attributeGroupsForType($typeId);

        if (!$typeId) {
            return [
                Placeholder::make('attributes_select_type')
                    ->content('Chọn phân mục để hiển thị nhóm thuộc tính tương ứng.')
                    ->columnSpanFull(),
            ];
        }

        if ($groups->isEmpty()) {
            return [
                Placeholder::make('attributes_empty')
                    ->label('Thuộc tính')
                    ->content('Chưa cấu hình nhóm thuộc tính cho phân mục này. Vào Product Type → Nhóm thuộc tính để gán.')
                    ->columnSpanFull(),
            ];
        }

        $fields = [];
        foreach ($groups as $group) {
            $fieldName = "attributes_{$group->id}";

            // Nhập tay: hiển thị TextInput (text/number)
            if ($group->filter_type === 'nhap_tay') {
                $input = TextInput::make($fieldName)
                    ->label($group->name);

                if ($group->input_type === 'number') {
                    $input->numeric()->step('any');
                } else {
                    $input->maxLength(255);
                }

                $fields[] = $input;
                continue;
            }

            $isMultiple = $group->filter_type === 'chon_nhieu';

            $field = Select::make($fieldName)
                ->label($group->name)
                ->options($group->terms->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->hidden(fn () => $group->terms->isEmpty());

            if ($isMultiple) {
                $field->multiple();
            }

            $fields[] = $field;
        }

        return $fields;
    }

    public static function attributeGroupsForType(?int $typeId): Collection
    {
        if ($typeId) {
            $type = ProductType::query()
                ->with(['attributeGroups' => function ($query) {
                    $query->with(['terms' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
                        ->orderByPivot('position')
                        ->orderBy('position');
                }])
                ->find($typeId);

            return $type?->attributeGroups ?? collect();
        }

        return CatalogAttributeGroup::query()
            ->with(['terms' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get();
    }

    protected static function clearAttributeFieldStates(callable $set): void
    {
        if (static::$attributeFieldKeys === null) {
            static::$attributeFieldKeys = CatalogAttributeGroup::query()
                ->pluck('id')
                ->map(fn (int $id) => "attributes_{$id}")
                ->all();
        }

        foreach (static::$attributeFieldKeys as $field) {
            $set($field, null);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['terms.group', 'images']))
            ->columns([
                static::getRowNumberColumn(),
                Tables\Columns\ImageColumn::make('product_image')
                    ->label('Ảnh')
                    ->disk('public')
                    ->width(60)
                    ->height(60)
                    ->getStateUsing(function ($record) {
                        // Lấy ảnh đầu tiên (order nhỏ nhất) hoặc cover image (order = 0)
                        $image = $record->images->sortBy('order')->first();
                        return $image ? $image->file_path : null;
                    })
                    ->defaultImageUrl('/images/placeholder.png'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->name),
                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Danh mục')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Phân mục')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('attributes')
                    ->label('Thuộc tính')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('terms', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (!$record->relationLoaded('terms') || $record->terms->isEmpty()) {
                            return [];
                        }

                        $grouped = $record->terms->groupBy(function ($term) {
                            return $term->group ? $term->group->name : 'Khác';
                        });

                        $result = [];
                        foreach ($grouped as $groupName => $terms) {
                            $termNames = $terms->pluck('name')->join(', ');
                            $result[] = "{$groupName}: {$termNames}";
                        }

                        return $result;
                    })
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Giá')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('original_price')
                    ->label('Giá gốc')
                    ->money('VND')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('active')
                    ->label('Hoạt động')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ngày cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }

    public static function getNavigationBadge(): ?string
    {
        $activeCount = static::getModel()::query()
            ->where('active', true)
            ->count();

        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getRelations(): array
    {
        return [
            ProductResource\RelationManagers\ImagesRelationManager::class,
            // ProductResource\RelationManagers\ProductTermAssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * Get frontend URL for a product
     * Uses FRONTEND_URL from .env config
     */
    public static function getFrontendUrl(Product $product): string
    {
        $frontendBaseUrl = config('app.frontend_url', 'http://localhost:3000');
        
        return $frontendBaseUrl . '/san-pham/' . $product->slug;
    }
}

