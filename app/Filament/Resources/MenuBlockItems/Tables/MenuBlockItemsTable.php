<?php

namespace App\Filament\Resources\MenuBlockItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MenuBlockItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Eager loading để tránh N+1 query
            ->modifyQueryUsing(fn ($query) => $query->with(['block.menu', 'term']))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('block.menu.title')
                    ->label('Menu')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-bars-3')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('block.title')
                    ->label('Khối menu')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-rectangle-group')
                    ->sortable(),
                ImageColumn::make('icon_image')
                    ->label('Icon')
                    ->disk('public')
                    ->width(50)
                    ->height(50)
                    ->defaultImageUrl(fn () => null)
                    ->circular(),
                TextColumn::make('label')
                    ->label('Nhãn hiển thị')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-tag')
                    ->color('primary')
                    ->placeholder('(Từ thuật ngữ)')
                    ->description(fn ($record) => $record->href ? "→ {$record->href}" : '(Auto từ term)'),
                TextColumn::make('term.name')
                    ->label('Thuật ngữ')
                    ->badge()
                    ->color('purple')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('badge')
                    ->label('Badge đặc biệt')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
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
                SelectFilter::make('menu_block_id')
                    ->label('Lọc theo Khối menu')
                    ->relationship('block', 'title')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('term_id')
                    ->label('Lọc theo Thuật ngữ')
                    ->relationship('term', 'name')
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
