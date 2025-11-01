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

    protected static ?string $modelLabel = 'Sự kiện theo dõi';

    protected static ?string $pluralModelLabel = 'Các sự kiện theo dõi';

    protected static ?string $navigationLabel = 'Sự kiện theo dõi';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-eye';

    protected static UnitEnum | string | null $navigationGroup = 'Phân tích';

    protected static ?int $navigationSort = 1;

    public static function schema(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
        TextColumn::make('id')->sortable()->label('ID'),
        TextColumn::make('event_type')
        ->badge()
        ->label('Loại sự kiện')
        ->color(fn (string $state): string => match ($state) {
        TrackingEvent::TYPE_PRODUCT_VIEW => 'success',
        TrackingEvent::TYPE_ARTICLE_VIEW => 'info',
        TrackingEvent::TYPE_CTA_CONTACT => 'warning',
            default => 'gray',
            }),
        TextColumn::make('visitor.anon_id')->label('Khách truy cập'),
        TextColumn::make('session.id')->label('Phiên'),
        TextColumn::make('product.name')->label('Sản phẩm')->placeholder('Không có'),
        TextColumn::make('article.title')->label('Bài viết')->placeholder('Không có'),
        TextColumn::make('occurred_at')->dateTime()->sortable()->label('Thời gian xảy ra'),
            Tables\Columns\ViewColumn::make('metadata')->view('filament.tables.columns.metadata')->label('Thông tin bổ sung'),
            ])
            ->filters([
            Tables\Filters\SelectFilter::make('event_type')
            ->label('Loại sự kiện')
            ->options([
            TrackingEvent::TYPE_PRODUCT_VIEW => 'Xem sản phẩm',
            TrackingEvent::TYPE_ARTICLE_VIEW => 'Xem bài viết',
                TrackingEvent::TYPE_CTA_CONTACT => 'Liên hệ qua CTA',
                ]),
            Tables\Filters\Filter::make('created_today')
                    ->label('Hôm nay')
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
