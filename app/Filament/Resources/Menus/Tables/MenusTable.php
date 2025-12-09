<?php

namespace App\Filament\Resources\Menus\Tables;

use App\Filament\Resources\BaseResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('blocks'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                BaseResource::getRowNumberColumn(),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-bars-3')
                    ->color('primary'),
                TextColumn::make('href')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->href)
                    ->color('gray'),
                TextColumn::make('type')
                    ->label('Kiểu')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'standard' => 'Link đơn',
                        'mega' => 'Mega Menu',
                        default => $state
                    })
                    ->icon(fn ($state) => match($state) {
                        'standard' => 'heroicon-o-link',
                        'mega' => 'heroicon-o-squares-2x2',
                        default => 'heroicon-o-bars-3'
                    })
                    ->color(fn ($state) => match($state) {
                        'standard' => 'gray',
                        'mega' => 'info',
                        default => 'gray'
                    })
                    ->sortable(),
                TextColumn::make('blocks_count')
                    ->label('Số cột')
                    ->counts('blocks')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->alignCenter()
                    ->icon('heroicon-o-rectangle-group'),
                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                //
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
