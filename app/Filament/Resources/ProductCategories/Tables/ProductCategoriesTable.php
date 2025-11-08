<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('products'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên nhóm')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('slug')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->copyable()
                    ->tooltip('Click để copy'),
                TextColumn::make('products_count')
                    ->label('Số sản phẩm')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }
}
