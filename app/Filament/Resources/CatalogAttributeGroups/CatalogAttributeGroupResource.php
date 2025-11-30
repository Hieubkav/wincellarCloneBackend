<?php

namespace App\Filament\Resources\CatalogAttributeGroups;

use App\Filament\Resources\CatalogAttributeGroups\Pages\CreateCatalogAttributeGroup;
use App\Filament\Resources\CatalogAttributeGroups\Pages\EditCatalogAttributeGroup;
use App\Filament\Resources\CatalogAttributeGroups\Pages\ListCatalogAttributeGroups;
use App\Filament\Resources\CatalogAttributeGroups\Pages\ViewCatalogAttributeGroup;
use App\Filament\Resources\CatalogAttributeGroups\Schemas\CatalogAttributeGroupForm;
use App\Filament\Resources\CatalogAttributeGroups\Schemas\CatalogAttributeGroupInfolist;
use App\Filament\Resources\CatalogAttributeGroups\Tables\CatalogAttributeGroupsTable;
use App\Filament\Resources\CatalogAttributeGroups\RelationManagers;
use App\Models\CatalogAttributeGroup;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CatalogAttributeGroupResource extends Resource
{
    protected static ?string $model = CatalogAttributeGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'Sản phẩm';

    protected static ?string $navigationLabel = 'Nhóm thuộc tính';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'Nhóm thuộc tính';

    protected static ?string $pluralModelLabel = 'Các nhóm thuộc tính';

    public static function form(Schema $schema): Schema
    {
        return CatalogAttributeGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CatalogAttributeGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatalogAttributeGroupsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TermsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatalogAttributeGroups::route('/'),
            'create' => CreateCatalogAttributeGroup::route('/create'),
            'edit' => EditCatalogAttributeGroup::route('/{record}/edit'),
        ];
    }
}
