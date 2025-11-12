<?php

namespace App\Filament\Resources\Images\Tables;


use App\Filament\Resources\BaseResource;use App\Models\Article;
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
                BaseResource::getRowNumberColumn(),
                ImageColumn::make('url')
                    ->label('Xem trước')
                    ->state(fn (Image $record): ?string => $record->url)
                    ->size(80)
                    ->defaultImageUrl('/images/placeholder.png')
                    ->toggleable(),
                TextColumn::make('file_path')
                    ->label('Đường dẫn')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('dimensions')
                    ->label('Kích thước')
                    ->state(fn (Image $record): string => 
                        $record->width && $record->height 
                            ? "{$record->width}x{$record->height}px" 
                            : '-'
                    )
                    ->toggleable(),
                TextColumn::make('disk')
                    ->label('Nơi lưu')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('model_type')
                    ->label('Loại chủ sở hữu')
                    ->formatStateUsing(fn (?string $state): ?string => self::formatModelType($state))
                    ->badge()
                    ->color(fn (?string $state): string => match($state) {
                        'App\Models\Product' => 'success',
                        'App\Models\Article' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('order')
                    ->label('Thứ tự')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'warning' : 'gray')
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? 'Cover' : (string) $state)
                    ->sortable()
                    ->toggleable(),
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
                \Filament\Actions\DeleteAction::make()
                    ->iconButton()
                    ->before(function (\Filament\Actions\DeleteAction $action, Image $record) {
                        // Kiểm tra xem ảnh có đang được dùng không
                        if ($record->model_type && $record->model_id) {
                            $ownerType = class_basename($record->model_type);
                            
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Không thể xóa ảnh')
                                ->body("Ảnh này đang được sử dụng bởi {$ownerType} #{$record->model_id}. Vui lòng gỡ liên kết trước khi xóa.")
                                ->persistent()
                                ->send();
                            
                            // Cancel deletion
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, $records) {
                            // Kiểm tra xem có ảnh nào đang được dùng không
                            $inUse = $records->filter(fn (Image $img) => $img->model_type && $img->model_id);
                            
                            if ($inUse->isNotEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Không thể xóa một số ảnh')
                                    ->body("Có {$inUse->count()} ảnh đang được sử dụng. Vui lòng gỡ liên kết trước khi xóa.")
                                    ->persistent()
                                    ->send();
                                
                                // Cancel deletion
                                $action->cancel();
                            }
                        }),
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
