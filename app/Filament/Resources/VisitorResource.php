<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorResource\Pages;
use App\Models\Visitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static ?string $modelLabel = 'Khách truy cập';

    protected static ?string $pluralModelLabel = 'Các khách truy cập';

    protected static ?string $navigationLabel = 'Khách truy cập';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static UnitEnum | string | null $navigationGroup = 'Phân tích';

    protected static ?int $navigationSort = 20;

    public static function schema(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->label('ID'),
                TextColumn::make('anon_id')->searchable()->label('Mã ẩn danh'),
                TextColumn::make('ip_hash')->placeholder('Không có')->label('Băm IP'),
                TextColumn::make('user_agent')->limit(50)->tooltip(fn ($record) => $record->user_agent)->label('Trình duyệt'),
                TextColumn::make('first_seen_at')->dateTime()->sortable()->label('Lần đầu thấy'),
                TextColumn::make('last_seen_at')->dateTime()->sortable()->label('Lần cuối thấy'),
                TextColumn::make('sessions_count')->counts('sessions')->label('Số phiên'),
                TextColumn::make('events_count')->counts('events')->label('Số sự kiện'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
            ])
            ->toolbarActions([
            //
            ])
            ->defaultSort('last_seen_at', 'desc')
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
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
        ];
    }
}
