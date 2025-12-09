<?php

namespace App\Filament\Resources\MenuBlockItems\Tables;

use App\Filament\Resources\BaseResource;
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
            ->modifyQueryUsing(fn ($query) => $query->with('block.menu'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                BaseResource::getRowNumberColumn(),
                TextColumn::make('block.menu.title')
                    ->label('Menu')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-bars-3')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('block.title')
                    ->label('Cột')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-rectangle-group')
                    ->sortable(),
                ImageColumn::make('icon_image')
                    ->label('Icon')
                    ->disk('public')
                    ->width(40)
                    ->height(40)
                    ->defaultImageUrl(fn () => null)
                    ->circular()
                    ->toggleable(),
                TextColumn::make('label')
                    ->label('Nhãn')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-tag')
                    ->color('primary'),
                TextColumn::make('href')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->href)
                    ->color('gray'),
                TextColumn::make('badge')
                    ->label('Badge')
                    ->badge()
                    ->color(fn ($state) => match(strtoupper($state ?? '')) {
                        'HOT' => 'danger',
                        'NEW' => 'success',
                        'SALE' => 'warning',
                        default => 'gray'
                    })
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('menu_block_id')
                    ->label('Lọc theo Cột')
                    ->relationship('block', 'title')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('active')
                    ->label('Trạng thái')
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
