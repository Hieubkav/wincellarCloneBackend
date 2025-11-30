<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Tables;

use App\Filament\Resources\BaseResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CatalogAttributeGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                BaseResource::getRowNumberColumn(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('filter_type')
                    ->label('Kiểu lọc')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(function (?string $state): ?string {
                        return match($state) {
                            'chon_don' => 'Chọn đơn',
                            'chon_nhieu' => 'Chọn nhiều',
                            'nhap_tay' => 'Nhập tay',
                            default => $state,
                        };
                    }),
                TextColumn::make('input_type')
                    ->label('Kiểu nhập')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (?string $state): ?string => match($state) {
                        'text' => 'Text',
                        'number' => 'Số',
                        default => null,
                    }),
                IconColumn::make('is_filterable')
                    ->label('Cho phép lọc')
                    ->boolean(),
                TextColumn::make('terms_count')
                    ->label('Số thuộc tính')
                    ->counts('terms')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('filter_type')
                    ->label('Kiểu lọc')
                    ->options([
                        'chon_don' => 'Chọn đơn',
                        'chon_nhieu' => 'Chọn nhiều',
                        'nhap_tay' => 'Nhập tay',
                    ]),
                TernaryFilter::make('is_filterable')
                    ->label('Cho phép lọc'),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }
}
