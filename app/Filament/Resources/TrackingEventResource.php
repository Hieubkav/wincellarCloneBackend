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
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;

class TrackingEventResource extends Resource
{
    protected static ?string $model = TrackingEvent::class;

    protected static ?string $modelLabel = 'Sự kiện theo dõi';

    protected static ?string $pluralModelLabel = 'Các sự kiện theo dõi';

    protected static ?string $navigationLabel = 'Sự kiện theo dõi';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-eye';

    protected static UnitEnum | string | null $navigationGroup = 'Phân tích';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = false;

    public static function schema(Schema $schema): Schema
    {
        return $schema;
    }

    public static function getNavigationBadge(): ?string
    {
        $todayCount = static::getModel()::query()
            ->whereDate('occurred_at', today())
            ->count();

        return $todayCount > 0 ? (string) $todayCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function table(Table $table): Table
    {
        return $table
            // Eager loading để tránh N+1 query
            ->modifyQueryUsing(fn (Builder $query) => 
                $query->with(['visitor', 'session', 'product', 'article'])
            )
            ->columns([
                static::getRowNumberColumn(),
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event_type')
                    ->label('Loại sự kiện')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        TrackingEvent::TYPE_PRODUCT_VIEW => 'Xem sản phẩm',
                        TrackingEvent::TYPE_ARTICLE_VIEW => 'Xem bài viết',
                        TrackingEvent::TYPE_CTA_CONTACT => 'Liên hệ CTA',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        TrackingEvent::TYPE_PRODUCT_VIEW => 'success',
                        TrackingEvent::TYPE_ARTICLE_VIEW => 'info',
                        TrackingEvent::TYPE_CTA_CONTACT => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->limit(30)
                    ->placeholder('(Không có)')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('article.title')
                    ->label('Bài viết')
                    ->limit(30)
                    ->placeholder('(Không có)')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('visitor.anon_id')
                    ->label('Khách truy cập')
                    ->limit(20)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('session.id')
                    ->label('Phiên')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('occurred_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->occurred_at->format('d/m/Y H:i')),
                TextColumn::make('metadata')
                    ->label('Thông tin bổ sung')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return '(Không có)';
                        }
                        if (is_array($state)) {
                            $items = [];
                            foreach ($state as $key => $value) {
                                $items[] = "{$key}: {$value}";
                            }
                            return implode(', ', array_slice($items, 0, 3));
                        }
                        return json_encode($state);
                    })
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Loại sự kiện')
                    ->options([
                        TrackingEvent::TYPE_PRODUCT_VIEW => 'Xem sản phẩm',
                        TrackingEvent::TYPE_ARTICLE_VIEW => 'Xem bài viết',
                        TrackingEvent::TYPE_CTA_CONTACT => 'Liên hệ qua CTA',
                    ]),
                Tables\Filters\Filter::make('occurred_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Từ ngày'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('occurred_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('occurred_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('today')
                    ->label('Hôm nay')
                    ->query(fn (Builder $query): Builder => $query->whereDate('occurred_at', today())),
                Tables\Filters\Filter::make('yesterday')
                    ->label('Hôm qua')
                    ->query(fn (Builder $query): Builder => $query->whereDate('occurred_at', today()->subDay())),
                Tables\Filters\Filter::make('last_7_days')
                    ->label('7 ngày qua')
                    ->query(fn (Builder $query): Builder => $query->where('occurred_at', '>=', today()->subDays(7))),
            ])
            ->recordActions([
                // ❌ KHÔNG có EditAction: Tracking events là analytics data, không nên sửa để giữ tính chính xác
                // ✅ Chỉ cho phép Delete để xóa test data hoặc data sai
                DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
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
