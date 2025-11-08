<?php

namespace App\Filament\Resources\MenuBlocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['menu', 'attributeGroup']))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('menu.title')
                    ->label('Menu cha')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Tiêu đề khối')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('attributeGroup.name')
                    ->label('Nhóm thuộc tính')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('max_terms')
                    ->label('Giới hạn')
                    ->numeric()
                    ->sortable()
                    ->placeholder('Không giới hạn'),
                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('menu_id')
                    ->label('Menu cha')
                    ->relationship('menu', 'title')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('active')
                    ->label('Hiển thị'),
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
