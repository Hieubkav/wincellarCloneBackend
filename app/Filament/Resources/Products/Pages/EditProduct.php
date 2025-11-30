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
            Actions\Action::make('view_frontend')
                ->label('Web')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn() => ProductResource::getFrontendUrl($this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load attributes data
        $product = $this->record;
        $groups = ProductResource::attributeGroupsForType($product->type_id);
        $extraAttrs = $product->extra_attrs ?? [];

        foreach ($groups as $group) {
            // Nhập tay -> lấy giá trị từ extra_attrs
            if ($group->filter_type === 'nhap_tay') {
                $data["attributes_{$group->id}"] = $extraAttrs[$group->code]['value'] ?? null;
                continue;
            }

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
        $groups = ProductResource::attributeGroupsForType($product->type_id)->keyBy('id');
        $extraAttrs = $product->extra_attrs ?? [];
        
        // Xóa tất cả assignments cũ
        $product->termAssignments()->delete();
        
        // Tạo lại assignments từ data mới
        $position = 0;
        foreach ($data as $key => $value) {
            if (!str_starts_with($key, 'attributes_')) {
                continue;
            }

            $groupId = (int) str_replace('attributes_', '', $key);
            $group = $groups->get($groupId);
            if (!$group) {
                continue;
            }

            // Nhập tay -> lưu vào extra_attrs
            if ($group->filter_type === 'nhap_tay') {
                $cleanValue = is_string($value) ? trim($value) : $value;

                if ($cleanValue === '' || $cleanValue === null) {
                    unset($extraAttrs[$group->code]);
                    continue;
                }

                $extraAttrs[$group->code] = [
                    'label' => $group->name,
                    'value' => $group->input_type === 'number' ? (float) $cleanValue : $cleanValue,
                    'type' => $group->input_type ?? 'text',
                ];

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

        // Cập nhật extra attributes
        $product->extra_attrs = $extraAttrs;
        $product->save();
    }
}
