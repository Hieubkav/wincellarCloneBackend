<?php

namespace App\Filament\Resources\ProductTypes;

use App\Filament\Resources\ProductTypes\Pages\CreateProductType;
use App\Filament\Resources\ProductTypes\Pages\EditProductType;
use App\Filament\Resources\ProductTypes\Pages\ListProductTypes;
use App\Filament\Resources\ProductTypes\Pages\ViewProductType;
use App\Filament\Resources\ProductTypes\Schemas\ProductTypeForm;
use App\Filament\Resources\ProductTypes\Schemas\ProductTypeInfolist;
use App\Filament\Resources\ProductTypes\Tables\ProductTypesTable;
use App\Models\ProductType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductTypeResource extends Resource
{
    protected static ?string $model = ProductType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'Sản phẩm';

    protected static ?string $navigationLabel = 'Loại sản phẩm';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Loại sản phẩm';

    protected static ?string $pluralModelLabel = 'Các loại sản phẩm';

    public static function form(Schema $schema): Schema
    {
        return ProductTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductTypesTable::configure($table);
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
        return 'primary';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductTypes::route('/'),
            'create' => CreateProductType::route('/create'),
            'view' => ViewProductType::route('/{record}'),
            'edit' => EditProductType::route('/{record}/edit'),
        ];
    }
}

