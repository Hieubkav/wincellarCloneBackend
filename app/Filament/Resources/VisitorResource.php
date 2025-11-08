<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorResource\Pages;
use App\Models\Visitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
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

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => 
                $query->withCount(['sessions', 'events'])
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('anon_id')
                    ->label('Mã ẩn danh')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('ip_hash')
                    ->label('Băm IP')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_agent')
                    ->label('Trình duyệt')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->user_agent)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('first_seen_at')
                    ->label('Lần đầu thấy')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_seen_at')
                    ->label('Lần cuối thấy')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('sessions_count')
                    ->label('Số phiên')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('events_count')
                    ->label('Số sự kiện')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // ❌ KHÔNG có EditAction: Visitor data là analytics data, không nên sửa để giữ tính chính xác
                // ✅ Chỉ cho phép Delete để xóa test data hoặc data sai
                DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
