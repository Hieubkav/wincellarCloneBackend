<?php

namespace App\Filament\Resources\HomeComponents\Tables;

use App\Enums\HomeComponentType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HomeComponentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width('50px')
                    ->alignCenter(),
                TextColumn::make('type')
                    ->label('Loại khối')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->badge()
                    ->icon(fn (string $state): string => HomeComponentType::tryFrom($state)?->getIcon() ?? 'heroicon-o-question-mark-circle')
                    ->formatStateUsing(fn (string $state): string => HomeComponentType::tryFrom($state)?->getLabel() ?? $state)
                    ->color('primary')
                    ->description(fn ($record): string => HomeComponentType::tryFrom($record->type)?->getDescription() ?? ''),
                TextColumn::make('config_summary')
                    ->label('Nội dung')
                    ->state(function ($record) {
                        $config = $record->config ?? [];
                        $type = $record->type;
                        
                        return match ($type) {
                            'hero_carousel' => count($config['slides'] ?? []) . ' slides',
                            'dual_banner' => count($config['banners'] ?? []) . ' banners',
                            'category_grid' => count($config['categories'] ?? []) . ' danh mục',
                            'favourite_products' => count($config['products'] ?? []) . ' sản phẩm',
                            'brand_showcase' => count($config['brands'] ?? []) . ' thương hiệu',
                            'collection_showcase' => ($config['title'] ?? '') . ' - ' . count($config['products'] ?? []) . ' sản phẩm',
                            'editorial_spotlight' => count($config['articles'] ?? []) . ' bài viết',
                            'footer' => $config['company_name'] ?? 'Footer',
                            default => '—'
                        };
                    })
                    ->placeholder('—')
                    ->color('gray'),
                ToggleColumn::make('active')
                    ->label('Hiển thị')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Loại')
                    ->options(HomeComponentType::options()),
                TernaryFilter::make('active')
                    ->label('Hiển thị'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }
}
