<?php

namespace App\Filament\Resources\Menus\Tables;

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
            // Eager loading để tránh N+1 query
            ->modifyQueryUsing(fn ($query) => $query->with('term'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->placeholder('(Từ thuật ngữ)'),
                TextColumn::make('term.name')
                    ->label('Thuật ngữ')
                    ->badge()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Kiểu')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'standard' => 'Thường',
                        'mega' => 'Mega',
                        default => $state
                    })
                    ->color(fn ($state) => match($state) {
                        'standard' => 'gray',
                        'mega' => 'info',
                        default => 'gray'
                    })
                    ->sortable(),
                TextColumn::make('href')
                    ->label('Đường dẫn')
                    ->limit(50)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
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
