<?php

namespace App\Filament\Resources\MenuBlockItems\Pages;

use App\Filament\Resources\MenuBlockItems\MenuBlockItemResource;
use App\Models\Image;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuBlockItem extends CreateRecord
{
    protected static string $resource = MenuBlockItemResource::class;

    private ?string $iconImage = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Lưu icon_image trước khi loại bỏ khỏi data
        $this->iconImage = $data['icon_image'] ?? null;
        unset($data['icon_image']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $menuBlockItem = $this->record;

        // Lưu icon image nếu có
        if ($this->iconImage) {
            Image::create([
                'file_path' => $this->iconImage,
                'disk' => 'public',
                'model_type' => get_class($menuBlockItem),
                'model_id' => $menuBlockItem->id,
                'order' => 0,
                'active' => true,
            ]);
        }
    }
}
