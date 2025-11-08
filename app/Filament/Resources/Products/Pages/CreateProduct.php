<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Lọc bỏ các attributes fields khỏi data chính
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'attributes_')) {
                unset($data[$key]);
            }
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $product = $this->record;
        $data = $this->data; // Dùng $this->data thay vì $this->form->getState()
        
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
