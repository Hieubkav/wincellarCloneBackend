<?php

namespace App\Filament\Resources\HomeComponents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
            ->defaultSort('order')
            ->columns([
                TextColumn::make('type')
                    ->label('Component')
                    ->searchable()
                    ->weight('medium')
                    ->badge(),
                TextColumn::make('config')
                    ->label('Config keys')
                    ->state(fn ($record) => collect($record->config ?? [])->keys()->implode(', '))
                    ->placeholder('—')
                    ->tooltip('Comma separated list of configured keys')
                    ->toggleable(),
                TextColumn::make('order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(fn () => HomeComponentsTable::typeOptions()),
                TernaryFilter::make('active')
                    ->label('Active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
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
