<?php

namespace App\Filament\Resources\MenuBlockItems;

use App\Filament\Resources\MenuBlockItems\Pages\CreateMenuBlockItem;
use App\Filament\Resources\MenuBlockItems\Pages\EditMenuBlockItem;
use App\Filament\Resources\MenuBlockItems\Pages\ListMenuBlockItems;
use App\Filament\Resources\MenuBlockItems\Pages\ViewMenuBlockItem;
use App\Filament\Resources\MenuBlockItems\Schemas\MenuBlockItemForm;
use App\Filament\Resources\MenuBlockItems\Schemas\MenuBlockItemInfolist;
use App\Filament\Resources\MenuBlockItems\Tables\MenuBlockItemsTable;
use App\Models\MenuBlockItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MenuBlockItemResource extends Resource
{
    protected static ?string $model = MenuBlockItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'label';

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
            'view' => ViewMenuBlockItem::route('/{record}'),
            'edit' => EditMenuBlockItem::route('/{record}/edit'),
        ];
    }
}
