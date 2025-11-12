<?php

namespace App\Filament\Resources\Menus\Tables;


use App\Filament\Resources\BaseResource;use Filament\Actions\BulkActionGroup;
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
            ->modifyQueryUsing(fn ($query) => $query->with('term')->withCount('blocks'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                BaseResource::getRowNumberColumn(),
                TextColumn::make('title')
                    ->label('Tiêu đề menu')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-bars-3')
                    ->color('primary')
                    ->placeholder('(Từ thuật ngữ)')
                    ->description(fn ($record) => $record->href ? "→ {$record->href}" : null),
                TextColumn::make('term.name')
                    ->label('Thuật ngữ')
                    ->badge()
                    ->color('purple')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->label('Kiểu menu')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'standard' => 'Thường',
                        'mega' => 'Mega Menu',
                        default => $state
                    })
                    ->icon(fn ($state) => match($state) {
                        'standard' => 'heroicon-o-bars-3',
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
                    ->label('Số khối')
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
