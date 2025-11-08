<?php

namespace App\Filament\Resources\HomeComponents;

use App\Filament\Resources\HomeComponents\Pages\CreateHomeComponent;
use App\Filament\Resources\HomeComponents\Pages\EditHomeComponent;
use App\Filament\Resources\HomeComponents\Pages\ListHomeComponents;
use App\Filament\Resources\HomeComponents\Schemas\HomeComponentForm;
use App\Filament\Resources\HomeComponents\Schemas\HomeComponentInfolist;
use App\Filament\Resources\HomeComponents\Tables\HomeComponentsTable;
use App\Models\HomeComponent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HomeComponentResource extends Resource
{
    protected static ?string $model = HomeComponent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'type';

    protected static UnitEnum|string|null $navigationGroup = 'Nội dung';

    protected static ?string $navigationLabel = 'Thành phần trang chủ';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Thành phần trang chủ';

    protected static ?string $pluralModelLabel = 'Các thành phần trang chủ';

    public static function form(Schema $schema): Schema
    {
        return HomeComponentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HomeComponentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HomeComponentsTable::configure($table);
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
            'index' => ListHomeComponents::route('/'),
            'create' => CreateHomeComponent::route('/create'),
            'edit' => EditHomeComponent::route('/{record}/edit'),
        ];
    }
}
