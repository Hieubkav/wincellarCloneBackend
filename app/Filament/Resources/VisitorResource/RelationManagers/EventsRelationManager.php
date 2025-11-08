<?php

namespace App\Filament\Resources\VisitorResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('event_type')->badge(),
                TextColumn::make('product.name')->label('Product'),
                TextColumn::make('article.title')->label('Article'),
                TextColumn::make('occurred_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
            ])
            ->toolbarActions([
            //
            ])
            ->defaultSort('occurred_at', 'desc');
    }
}
