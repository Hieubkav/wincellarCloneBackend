<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load attributes data
        $product = $this->record;
        $groups = \App\Models\CatalogAttributeGroup::all();
        
        foreach ($groups as $group) {
            $termIds = $product->termAssignments()
                ->whereHas('term', fn($q) => $q->where('group_id', $group->id))
                ->pluck('term_id')
                ->toArray();
            
            if ($group->filter_type === 'chon_nhieu') {
                $data["attributes_{$group->id}"] = $termIds;
            } else {
                $data["attributes_{$group->id}"] = $termIds[0] ?? null;
            }
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Lọc bỏ các attributes fields khỏi data chính
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'attributes_')) {
                unset($data[$key]);
            }
        }
        
        return $data;
    }

    protected function afterSave(): void
    {
        $product = $this->record;
        $data = $this->data;
        
        // Xóa tất cả assignments cũ
        $product->termAssignments()->delete();
        
        // Tạo lại assignments từ data mới
        $position = 0;
        foreach ($data as $key => $value) {
            if (!str_starts_with($key, 'attributes_')) {
                continue;
            }
            
            if (empty($value)) {
                continue;
            }
            
            $termIds = is_array($value) ? $value : [$value];
            
            foreach ($termIds as $termId) {
                $product->termAssignments()->create([
                    'term_id' => $termId,
                    'position' => $position++,
                ]);
            }
        }
    }
}
