<?php

namespace App\Filament\Resources\Products\ProductResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ProductTermAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'termAssignments';
    
    protected static ?string $title = 'Thuộc tính sản phẩm';
    
    protected static ?string $modelLabel = 'thuộc tính';



    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn($query) => $query->with(['term.group']))
            ->columns([
                TextColumn::make('term.group.name')
                    ->label('Tên thuộc tính')
                    ->badge()
                    ->sortable(),
                TextColumn::make('term.name')
                    ->label('Giá trị')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
            //
            ])
            ->defaultSort('position', 'asc');
    }

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tạo')
                ->form([
                    Select::make('attribute_group_id')
                        ->label('Nhóm thuộc tính')
                        ->options(function () {
                            return \App\Models\CatalogAttributeGroup::orderBy('position')
                                ->get()
                                ->mapWithKeys(function ($group) {
                                    $type = $group->filter_type === 'chon_nhieu' ? '(Chọn nhiều)' : '(Chọn đơn)';
                                    return [$group->id => $group->name . ' ' . $type];
                                });
                        })
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('term_id', null)),
                    
                    Select::make('term_id')
                        ->label('Giá trị')
                        ->options(function (callable $get) {
                            $groupId = $get('attribute_group_id');
                            if (!$groupId) {
                                return [];
                            }
                            
                            return \App\Models\CatalogTerm::where('group_id', $groupId)
                                ->where('is_active', true)
                                ->orderBy('position')
                                ->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->hidden(fn (callable $get) => !$get('attribute_group_id')),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    // Bỏ attribute_group_id khỏi data vì không lưu vào database
                    unset($data['attribute_group_id']);
                    return $data;
                })
                ->before(function (CreateAction $action, array $data) {
                    // Lấy thông tin group để check
                    $term = \App\Models\CatalogTerm::find($data['term_id']);
                    if (!$term || !$term->group) {
                        return;
                    }

                    // Nếu là chọn đơn, xóa các assignment cũ của group này
                    if ($term->group->filter_type === 'chon_don') {
                        $product = $action->getRecord();
                        $product->termAssignments()
                            ->whereHas('term', function ($query) use ($term) {
                                $query->where('group_id', $term->group_id);
                            })
                            ->delete();
                    }
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->iconButton()
                ->form([
                    Select::make('attribute_group_id')
                        ->label('Nhóm thuộc tính')
                        ->options(function () {
                            return \App\Models\CatalogAttributeGroup::orderBy('position')
                                ->get()
                                ->mapWithKeys(function ($group) {
                                    $type = $group->filter_type === 'chon_nhieu' ? '(Chọn nhiều)' : '(Chọn đơn)';
                                    return [$group->id => $group->name . ' ' . $type];
                                });
                        })
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('term_id', null))
                        ->afterStateHydrated(function (Select $component, $state, $record) {
                            if ($record && $record->term) {
                                $component->state($record->term->group_id);
                            }
                        }),
                    
                    Select::make('term_id')
                        ->label('Giá trị')
                        ->options(function (callable $get) {
                            $groupId = $get('attribute_group_id');
                            if (!$groupId) {
                                return [];
                            }
                            
                            return \App\Models\CatalogTerm::where('group_id', $groupId)
                                ->where('is_active', true)
                                ->orderBy('position')
                                ->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->hidden(fn (callable $get) => !$get('attribute_group_id')),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    // Bỏ attribute_group_id khỏi data vì không lưu vào database
                    unset($data['attribute_group_id']);
                    return $data;
                })
                ->before(function (EditAction $action, array $data) {
                    // Lấy thông tin group để check
                    $term = \App\Models\CatalogTerm::find($data['term_id']);
                    if (!$term || !$term->group) {
                        return;
                    }

                    // Nếu là chọn đơn, xóa các assignment khác của group này (trừ record hiện tại)
                    if ($term->group->filter_type === 'chon_don') {
                        $currentRecord = $action->getRecord();
                        $product = $currentRecord->product;
                        
                        $product->termAssignments()
                            ->where('id', '!=', $currentRecord->id)
                            ->whereHas('term', function ($query) use ($term) {
                                $query->where('group_id', $term->group_id);
                            })
                            ->delete();
                    }
                }),
            DeleteAction::make()->iconButton(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
