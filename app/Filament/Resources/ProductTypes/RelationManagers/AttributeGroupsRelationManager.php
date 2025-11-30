<?php

namespace App\Filament\Resources\ProductTypes\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttributeGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'attributeGroups';

    protected static ?string $title = 'Nhóm thuộc tính';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('pivot.position')
                    ->label('Vị trí')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->step(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('catalog_attribute_group_product_type.position')
            ->reorderable('catalog_attribute_group_product_type.position')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('filter_type')
                    ->label('Kiểu lọc')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'chon_don' => 'Chọn đơn',
                        'chon_nhieu' => 'Chọn nhiều',
                        'nhap_tay' => 'Nhập tay',
                        default => $state,
                    }),
                TextColumn::make('code')
                    ->label('Code')
                    ->badge(),
                TextColumn::make('pivot.position')
                    ->label('Vị trí')
                    ->numeric()
                    ->sortable(
                        query: fn (Builder $query, string $direction): Builder => $query->orderBy(
                            'catalog_attribute_group_product_type.position',
                            $direction,
                        ),
                    ),
            ]);
    }

    protected function getTableHeaderActions(): array
    {
        return [
            AttachAction::make()
                ->label('Thêm nhóm thuộc tính')
                ->multiple()
                ->recordTitleAttribute('name')
                ->preloadRecordSelect()
                ->recordSelectSearchColumns(['name', 'code'])
                ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('catalog_attribute_groups.position'))
                ->form(fn (AttachAction $action): array => [
                    // Giữ Select mặc định của Filament để chọn nhóm cần gán.
                    $action->getRecordSelect(),
                    TextInput::make('pivot.position')
                        ->label('Vị trí')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->step(1),
                ]),
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
