<?php

namespace App\Filament\Resources\CatalogTerms\RelationManagers;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Sản phẩm';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make()
                    ->schema([
                        Toggle::make('pivot.is_primary')
                            ->label('Là thuộc tính chính')
                            ->inline(false),
                        TextInput::make('pivot.position')
                            ->label('Thứ tự')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1),
                    ])
                    ->columns(2),
                KeyValue::make('pivot.extra')
                    ->label('Dữ liệu bổ sung')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productCategory.name')
                    ->label('Danh mục')
                    ->badge()
                    ->toggleable(),
                IconColumn::make('pivot.is_primary')
                    ->label('Là chính')
                    ->boolean(),
                TextColumn::make('pivot.position')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
            ]);
    }

    protected function getTableHeaderActions(): array
    {
        return [
            AttachAction::make()
                ->label('Gán sản phẩm')
                ->preloadRecordSelect()
                ->recordSelectSearchColumns(['name', 'slug'])
                ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('name')),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make(),
            DetachAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DetachBulkAction::make(),
            ]),
        ];
    }
}
