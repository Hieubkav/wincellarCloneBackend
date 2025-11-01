<?php

namespace App\Filament\Resources\Products;

use BackedEnum;
use UnitEnum;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'Nội dung';

    protected static ?string $navigationLabel = 'Sản phẩm';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Sản phẩm';

    protected static ?string $pluralModelLabel = 'Các sản phẩm';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->rules(['alpha_dash']),
                        Select::make('product_category_id')
                            ->label('Category')
                            ->options(ProductCategory::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('type_id')
                            ->label('Type')
                            ->options(ProductType::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('price')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('original_price')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('alcohol_percent')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.1),
                        TextInput::make('volume_ml')
                            ->numeric()
                            ->minValue(0),
                        Textarea::make('description')
                            ->rows(3),
                        Toggle::make('active')
                            ->default(true),
                    ])
                    ->columns(2),
                Grid::make()
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->rows(2)
                            ->maxLength(255),
                    ])
                    ->columns(2),
                // Gallery will be handled by HasMediaGallery trait
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
        Tables\Columns\TextColumn::make('name')
        ->label('Tên')
        ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('slug')
        ->label('Slug')
            ->searchable()
        ->sortable(),
        Tables\Columns\TextColumn::make('productCategory.name')
            ->label('Danh mục')
        ->badge(),
        Tables\Columns\TextColumn::make('type.name')
            ->label('Loại')
        ->badge(),
        Tables\Columns\TextColumn::make('price')
            ->label('Giá')
        ->money('VND')
            ->sortable(),
        Tables\Columns\IconColumn::make('active')
        ->label('Hoạt động')
        ->boolean(),
            Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ]);
    }

    public static function getTableActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
        ];
    }

    public static function getTableBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ProductResource\RelationManagers\ProductTermAssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
