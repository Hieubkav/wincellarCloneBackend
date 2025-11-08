<?php

namespace App\Filament\Resources\MenuBlockItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuBlockItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Eager loading để tránh N+1 query
            ->modifyQueryUsing(fn ($query) => $query->with(['block', 'term']))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('block.title')
                    ->label('Khối menu')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('label')
                    ->label('Nhãn')
                    ->searchable()
                    ->sortable()
                    ->placeholder('(Từ thuật ngữ)'),
                TextColumn::make('term.name')
                    ->label('Thuật ngữ')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('href')
                    ->label('Đường dẫn')
                    ->limit(50)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('badge')
                    ->label('Nhãn đặc biệt')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),
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
