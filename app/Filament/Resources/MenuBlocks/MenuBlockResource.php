<?php

namespace App\Filament\Resources\MenuBlocks;

use App\Filament\Resources\MenuBlocks\Pages\CreateMenuBlock;
use App\Filament\Resources\MenuBlocks\Pages\EditMenuBlock;
use App\Filament\Resources\MenuBlocks\Pages\ListMenuBlocks;
use App\Filament\Resources\MenuBlocks\Pages\ViewMenuBlock;
use App\Filament\Resources\MenuBlocks\Schemas\MenuBlockForm;
use App\Filament\Resources\MenuBlocks\Schemas\MenuBlockInfolist;
use App\Filament\Resources\MenuBlocks\Tables\MenuBlocksTable;
use App\Models\MenuBlock;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MenuBlockResource extends Resource
{
    protected static ?string $model = MenuBlock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?string $navigationLabel = 'Khối menu';

    protected static ?int $navigationSort = 70;

    protected static ?string $modelLabel = 'Khối menu';

    protected static ?string $pluralModelLabel = 'Các khối menu';

    public static function form(Schema $schema): Schema
    {
        return MenuBlockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MenuBlockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenuBlocksTable::configure($table);
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
            'index' => ListMenuBlocks::route('/'),
            'create' => CreateMenuBlock::route('/create'),
            'view' => ViewMenuBlock::route('/{record}'),
            'edit' => EditMenuBlock::route('/{record}/edit'),
        ];
    }
}
