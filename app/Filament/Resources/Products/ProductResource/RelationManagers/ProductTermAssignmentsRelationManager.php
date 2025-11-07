<?php

namespace App\Filament\Resources\Products\ProductResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;

class ProductTermAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'termAssignments';
    
    protected static ?string $title = 'Thuộc tính sản phẩm';
    
    protected static ?string $modelLabel = 'thuộc tính';

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('term_id')
                    ->label('Thuộc tính')
                    ->relationship('term', 'name')
                    ->required(),
                Toggle::make('is_primary')
                    ->label('Là chính'),
                TextInput::make('position')
                    ->label('Thứ tự')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('term.name')
                    ->label('Tên thuộc tính'),
                BooleanColumn::make('is_primary')
                    ->label('Là chính'),
                TextColumn::make('position')
                    ->label('Thứ tự'),
            ])
            ->filters([
            //
            ]);
    }

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
