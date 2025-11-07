<?php

namespace App\Filament\Resources\CatalogTerms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CatalogTermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['group', 'parent'])->withCount('products'))
            ->defaultSort('position')
            ->columns([
                TextColumn::make('group.name')
                    ->label('Group')
                    ->badge()
                    ->sortable()
                    ->toggleable(false),
                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('Top level')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->badge(),
                TextColumn::make('products_count')
                    ->label('Products')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('position')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
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
                SelectFilter::make('group_id')
                    ->label('Group')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
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
}
