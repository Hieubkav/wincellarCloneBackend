<?php

namespace App\Filament\Resources\VisitorResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('started_at')->dateTime(),
                TextColumn::make('ended_at')->dateTime(),
                TextColumn::make('events_count')->counts('events'),
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
            ]);
    }
}
