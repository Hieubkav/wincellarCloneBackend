<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->badge()
                    ->copyable()
                    ->tooltip('Click to copy the slug'),
                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
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
            ]);
    }
}
