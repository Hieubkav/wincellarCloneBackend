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
use Illuminate\Database\Eloquent\Builder;

class CatalogAttributeGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->modifyQueryUsing(function (Builder $query) {
                $query->selectRaw("
                    catalog_attribute_groups.*,
                    CASE 
                        WHEN filter_type = 'nhap_tay' THEN (
                            SELECT COUNT(*) 
                            FROM products 
                            WHERE JSON_EXTRACT(products.extra_attrs, CONCAT('$.\"', catalog_attribute_groups.code, '\"')) IS NOT NULL
                        )
                        ELSE (
                            SELECT COUNT(DISTINCT pta.product_id)
                            FROM product_term_assignments pta
                            JOIN catalog_terms ct ON pta.term_id = ct.id
                            WHERE ct.group_id = catalog_attribute_groups.id
                        )
                    END as computed_products_count
                ");
            })
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
                        return match ($state) {
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
                    ->formatStateUsing(fn (?string $state): ?string => match ($state) {
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
                TextColumn::make('computed_products_count')
                    ->label('Số sản phẩm')
                    ->badge()
                    ->color('info'),
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
