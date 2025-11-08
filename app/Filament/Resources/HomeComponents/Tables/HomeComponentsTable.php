<?php

namespace App\Filament\Resources\HomeComponents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HomeComponentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('type')
                    ->label('Loại khối')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->badge(),
                TextColumn::make('config')
                    ->label('Cấu hình')
                    ->state(fn ($record) => collect($record->config ?? [])->keys()->implode(', '))
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
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
                SelectFilter::make('type')
                    ->label('Loại')
                    ->options(fn () => HomeComponentsTable::typeOptions()),
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

    protected static function typeOptions(): array
    {
        return \App\Models\HomeComponent::query()
            ->select('type')
            ->distinct()
            ->pluck('type', 'type')
            ->filter()
            ->toArray();
    }
}
