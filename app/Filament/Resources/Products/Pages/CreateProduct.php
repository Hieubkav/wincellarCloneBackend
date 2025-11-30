<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Image;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    private array $productImages = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Lưu product_images trước khi loại bỏ khỏi data
        $this->productImages = $data['product_images'] ?? [];
        
        // Lọc bỏ các attributes fields và product_images khỏi data chính
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'attributes_') || $key === 'product_images') {
                unset($data[$key]);
            }
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $product = $this->record;
        $data = $this->data;
        $groups = ProductResource::attributeGroupsForType($product->type_id)->keyBy('id');
        $extraAttrs = [];
        
        // Lưu term assignments hoặc extra attributes
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

            if ($group->filter_type === 'nhap_tay') {
                $cleanValue = is_string($value) ? trim($value) : $value;
                if ($cleanValue === '' || $cleanValue === null) {
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

        // Lưu extra attributes
        if (!empty($extraAttrs)) {
            $product->extra_attrs = $extraAttrs;
            $product->save();
        }

        // Lưu images
        if (!empty($this->productImages)) {
            $order = 0;
            foreach ($this->productImages as $filePath) {
                Image::create([
                    'file_path' => $filePath,
                    'disk' => 'public',
                    'model_type' => get_class($product),
                    'model_id' => $product->id,
                    'order' => $order,
                    'active' => true,
                ]);
                $order++;
            }
        }
    }
}
