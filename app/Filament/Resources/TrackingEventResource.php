<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackingEventResource\Pages;
use App\Models\TrackingEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class TrackingEventResource extends Resource
{
    protected static ?string $model = TrackingEvent::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-eye';

    protected static UnitEnum | string | null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 1;

    public static function schema(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        TrackingEvent::TYPE_PRODUCT_VIEW => 'success',
                        TrackingEvent::TYPE_ARTICLE_VIEW => 'info',
                        TrackingEvent::TYPE_CTA_CONTACT => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('visitor.anon_id')->label('Visitor'),
                TextColumn::make('session.id')->label('Session'),
                TextColumn::make('product.name')->label('Product')->placeholder('N/A'),
                TextColumn::make('article.title')->label('Article')->placeholder('N/A'),
                TextColumn::make('occurred_at')->dateTime()->sortable(),
                Tables\Columns\ViewColumn::make('metadata')->view('filament.tables.columns.metadata'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->options([
                        TrackingEvent::TYPE_PRODUCT_VIEW => 'Product View',
                        TrackingEvent::TYPE_ARTICLE_VIEW => 'Article View',
                        TrackingEvent::TYPE_CTA_CONTACT => 'CTA Contact',
                    ]),
                Tables\Filters\Filter::make('created_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('occurred_at', today())),
            ])
            ->recordActions([
                // No actions for read-only
            ])
            ->toolbarActions([
                // No bulk actions
            ])
            ->defaultSort('occurred_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrackingEvents::route('/'),
        ];
    }
}
