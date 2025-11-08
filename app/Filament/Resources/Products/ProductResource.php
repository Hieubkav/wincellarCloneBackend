<?php

namespace App\Filament\Resources\Products;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Malzariey\FilamentLexicalEditor\LexicalEditor;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

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
            ->schema([
                Tabs::make()
                    ->tabs([
                        Tabs\Tab::make('Thông tin chính')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Tên sản phẩm')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('categories')
                                    ->label('Danh mục')
                                    ->relationship('categories', 'name')
                                    ->options(ProductCategory::active()->pluck('name', 'id'))
                                    ->searchable()
                                    ->multiple()
                                    ->preload()
                                    ->required(),

                                Select::make('type_id')
                                    ->label('Loại sản phẩm')
                                    ->options(ProductType::active()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                LexicalEditor::make('description')
                                    ->label('Mô tả')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Giá & Thông số')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('price')
                                            ->label('Giá bán')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->prefix('₫'),

                                        TextInput::make('original_price')
                                            ->label('Giá gốc')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->prefix('₫'),

                                        TagsInput::make('badges')
                                            ->label('Nhãn hiển thị')
                                            ->suggestions(['HOT', 'NEW', 'SALE', 'LIMITED', 'BEST_SELLER'])
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Grid::make()
                                    ->schema([
                                        TextInput::make('alcohol_percent')
                                            ->label('Nồng độ cồn (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(0.1)
                                            ->suffix('%'),

                                        TextInput::make('volume_ml')
                                            ->label('Dung tích (ml)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->suffix('ml'),

                                        Toggle::make('active')
                                            ->label('Hoạt động')
                                            ->default(true),
                                    ])
                                    ->columns(3),
                            ]),

                        Tabs\Tab::make('Thuộc tính')
                            ->schema(static::getAttributeFields()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function getAttributeFields(): array
    {
        $groups = \App\Models\CatalogAttributeGroup::with(['terms' => function ($query) {
            $query->where('is_active', true)->orderBy('position');
        }])->orderBy('position')->get();

        $fields = [];
        foreach ($groups as $group) {
            $isMultiple = $group->filter_type === 'chon_nhieu';
            
            $field = Select::make("attributes_{$group->id}")
                ->label($group->name)
                ->options($group->terms->pluck('name', 'id'))
                ->searchable()
                ->preload();
            
            if ($isMultiple) {
                $field->multiple();
            }
            
            $fields[] = $field;
        }

        return $fields;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn($record) => $record->name),
                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Danh mục')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Loại sản phẩm')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('terms_count')
                    ->label('Thuộc tính')
                    ->counts('terms')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state . ' thuộc tính')
                    ->sortable()
                    ->tooltip('Số lượng thuộc tính (Brand, Origin, Grape...)'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Giá')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('original_price')
                    ->label('Giá gốc')
                    ->money('VND')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('alcohol_percent')
                    ->label('Nồng độ cồn')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('volume_ml')
                    ->label('Dung tích')
                    ->suffix(' ml')
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
}
