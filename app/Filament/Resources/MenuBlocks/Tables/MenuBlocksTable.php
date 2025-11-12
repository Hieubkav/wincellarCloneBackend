<?php

namespace App\Filament\Resources\MenuBlocks\Tables;


use App\Filament\Resources\BaseResource;use Filament\Actions\BulkActionGroup;
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['menu', 'attributeGroup'])->withCount('items'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                BaseResource::getRowNumberColumn(),
                TextColumn::make('menu.title')
                    ->label('Menu cha')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-bars-3')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Tiêu đề khối')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-rectangle-group')
                    ->color('info'),
                TextColumn::make('attributeGroup.name')
                    ->label('Nhóm thuộc tính')
                    ->badge()
                    ->color('purple')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('max_terms')
                    ->label('Giới hạn')
                    ->numeric()
                    ->sortable()
                    ->placeholder('∞')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('items_count')
                    ->label('Số mục')
                    ->counts('items')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->alignCenter()
                    ->icon('heroicon-o-list-bullet'),
                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
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
                    ->label('Lọc theo Menu')
                    ->relationship('menu', 'title')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('attribute_group_id')
                    ->label('Lọc theo Nhóm thuộc tính')
                    ->relationship('attributeGroup', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('active')
                    ->label('Trạng thái hiển thị')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hiển thị')
                    ->falseLabel('Đã ẩn'),
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
