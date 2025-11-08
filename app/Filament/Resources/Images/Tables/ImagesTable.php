<?php

namespace App\Filament\Resources\Images\Tables;

use App\Models\Article;
use App\Models\Image;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Eager loading để tránh N+1 query
            ->modifyQueryUsing(fn ($query) => $query->with('model'))
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                ImageColumn::make('url')
                    ->label('Xem trước')
                    ->state(fn (Image $record): ?string => $record->url)
                    ->square()
                    ->toggleable(),
                TextColumn::make('file_path')
                    ->label('Đường dẫn')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('disk')
                    ->label('Nơi lưu')
                    ->badge()
                    ->sortable(),
                TextColumn::make('model_type')
                    ->label('Loại chủ sở hữu')
                    ->formatStateUsing(fn (?string $state): ?string => self::formatModelType($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('model_id')
                    ->label('ID chủ sở hữu')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Xóa lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('model_type')
                    ->label('Loại chủ sở hữu')
                    ->options(self::modelTypeOptions())
                    ->searchable(),
                SelectFilter::make('disk')
                    ->label('Nơi lưu')
                    ->options(self::diskOptions())
                    ->searchable(),
                TernaryFilter::make('active')
                    ->label('Hiển thị'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                \Filament\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }

    protected static function modelTypeOptions(): array
    {
        $defaults = [
            Product::class => 'Product',
            Article::class => 'Article',
        ];

        $existing = Image::query()
            ->select('model_type')
            ->distinct()
            ->pluck('model_type')
            ->filter()
            ->mapWithKeys(fn (string $model): array => [$model => self::formatModelType($model)]);

        return collect($defaults)
            ->merge($existing)
            ->all();
    }

    protected static function diskOptions(): array
    {
        return collect(config('filesystems.disks', []))
            ->keys()
            ->mapWithKeys(fn (string $disk): array => [$disk => $disk])
            ->all();
    }

    protected static function formatModelType(?string $value): ?string
    {
        return $value ? class_basename($value) : null;
    }
}
