<?php

namespace App\Filament\Resources\MenuBlockItems\Pages;

use App\Filament\Resources\MenuBlockItems\MenuBlockItemResource;
use App\Models\Image;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuBlockItem extends EditRecord
{
    protected static string $resource = MenuBlockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load icon image hiện tại
        $menuBlockItem = $this->record;
        $iconImage = $menuBlockItem->coverImage;
        
        if ($iconImage) {
            $data['icon_image'] = $iconImage->file_path;
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Loại bỏ icon_image khỏi data chính
        $newIconPath = $data['icon_image'] ?? null;
        unset($data['icon_image']);
        
        // Lưu tạm để xử lý sau
        $this->newIconPath = $newIconPath;
        
        return $data;
    }

    private ?string $newIconPath = null;

    protected function afterSave(): void
    {
        $menuBlockItem = $this->record;

        // Xử lý icon image
        if ($this->newIconPath !== null) {
            $currentIcon = $menuBlockItem->coverImage;
            
            // Nếu đã có icon cũ
            if ($currentIcon) {
                if ($this->newIconPath) {
                    // Update icon mới
                    $currentIcon->update([
                        'file_path' => $this->newIconPath,
                    ]);
                } else {
                    // Xóa icon nếu user xóa
                    $currentIcon->delete();
                }
            } elseif ($this->newIconPath) {
                // Tạo icon mới nếu chưa có
                Image::create([
                    'file_path' => $this->newIconPath,
                    'disk' => 'public',
                    'model_type' => get_class($menuBlockItem),
                    'model_id' => $menuBlockItem->id,
                    'order' => 0,
                    'active' => true,
                ]);
            }
        }
    }
}
