<?php

namespace App\Filament\Resources\CatalogTerms;

use App\Filament\Resources\CatalogTerms\Pages\CreateCatalogTerm;
use App\Filament\Resources\CatalogTerms\Pages\EditCatalogTerm;
use App\Filament\Resources\CatalogTerms\Pages\ListCatalogTerms;
use App\Filament\Resources\CatalogTerms\Pages\ViewCatalogTerm;
use App\Filament\Resources\CatalogTerms\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\CatalogTerms\Schemas\CatalogTermForm;
use App\Filament\Resources\CatalogTerms\Schemas\CatalogTermInfolist;
use App\Filament\Resources\CatalogTerms\Tables\CatalogTermsTable;
use App\Models\CatalogTerm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CatalogTermResource extends Resource
{
    protected static ?string $model = CatalogTerm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'Sản phẩm';

    protected static ?string $navigationLabel = 'Thuộc tính sản phẩm';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Thuộc tính';

    protected static ?string $pluralModelLabel = 'Các thuộc tính';

    public static function form(Schema $schema): Schema
    {
        return CatalogTermForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CatalogTermInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatalogTermsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $activeCount = static::getModel()::query()
            ->where('is_active', true)
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
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatalogTerms::route('/'),
            'create' => CreateCatalogTerm::route('/create'),
            'view' => ViewCatalogTerm::route('/{record}'),
            'edit' => EditCatalogTerm::route('/{record}/edit'),
        ];
    }
}
