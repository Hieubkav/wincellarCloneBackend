<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorResource\Pages;
use App\Models\Visitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static UnitEnum | string | null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 3;

    public static function schema(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('anon_id')->searchable(),
                TextColumn::make('ip_hash')->placeholder('N/A'),
                TextColumn::make('user_agent')->limit(50)->tooltip(fn ($record) => $record->user_agent),
                TextColumn::make('first_seen_at')->dateTime()->sortable(),
                TextColumn::make('last_seen_at')->dateTime()->sortable(),
                TextColumn::make('sessions_count')->counts('sessions'),
                TextColumn::make('events_count')->counts('events'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
            //
            ])
            ->defaultSort('last_seen_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            VisitorResource\RelationManagers\SessionsRelationManager::class,
            VisitorResource\RelationManagers\EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitors::route('/'),
            'view' => Pages\ViewVisitor::route('/{record}'),
        ];
    }
}
