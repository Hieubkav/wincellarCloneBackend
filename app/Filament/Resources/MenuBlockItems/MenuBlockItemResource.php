<?php

namespace App\Filament\Resources\MenuBlockItems;

use App\Filament\Resources\MenuBlockItems\Pages\CreateMenuBlockItem;
use App\Filament\Resources\MenuBlockItems\Pages\EditMenuBlockItem;
use App\Filament\Resources\MenuBlockItems\Pages\ListMenuBlockItems;
use App\Filament\Resources\MenuBlockItems\Schemas\MenuBlockItemForm;
use App\Filament\Resources\MenuBlockItems\Schemas\MenuBlockItemInfolist;
use App\Filament\Resources\MenuBlockItems\Tables\MenuBlockItemsTable;
use App\Models\MenuBlockItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MenuBlockItemResource extends Resource
{
    protected static ?string $model = MenuBlockItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Mục khối menu';

    protected static UnitEnum|string|null $navigationGroup = 'Điều hướng';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Mục khối menu';

    protected static ?string $pluralModelLabel = 'Các mục khối menu';

    public static function form(Schema $schema): Schema
    {
        return MenuBlockItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MenuBlockItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenuBlockItemsTable::configure($table);
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenuBlockItems::route('/'),
            'create' => CreateMenuBlockItem::route('/create'),
            'edit' => EditMenuBlockItem::route('/{record}/edit'),
        ];
    }
}
