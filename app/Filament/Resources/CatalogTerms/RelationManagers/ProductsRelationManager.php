<?php

namespace App\Filament\Resources\CatalogTerms\RelationManagers;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
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
                TextInput::make('pivot.position')
                    ->label('Thứ tự')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->step(1),
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
                TextColumn::make('type.name')
                    ->label('Loại')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('pivot.position')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(
                        query: fn (Builder $query, string $direction): Builder => $query->orderBy(
                            'product_term_assignments.position',
                            $direction,
                        ),
                    ),
            ])
            ->defaultSort('product_term_assignments.position', 'asc');
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
            EditAction::make()->iconButton(),
            DetachAction::make()->iconButton(),
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
