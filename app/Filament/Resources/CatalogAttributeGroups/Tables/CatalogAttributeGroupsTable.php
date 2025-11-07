<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CatalogAttributeGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->extraAttributes(['class' => 'whitespace-nowrap']),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('filter_type')
                    ->label('Filter type')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (?string $state): ?string => $state ? ucfirst($state) : null),
                IconColumn::make('is_filterable')
                    ->label('Filterable')
                    ->boolean(),
                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
                TextColumn::make('terms_count')
                    ->label('Terms')
                    ->counts('terms')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('filter_type')
                    ->label('Filter type')
                    ->options([
                        'single' => 'Single',
                        'multi' => 'Multi',
                        'hierarchy' => 'Hierarchy',
                        'range' => 'Range',
                    ]),
                TernaryFilter::make('is_filterable')
                    ->label('Filterable'),
                TernaryFilter::make('is_primary')
                    ->label('Primary'),
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
}
