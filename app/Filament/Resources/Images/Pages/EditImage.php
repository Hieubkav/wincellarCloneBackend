<?php

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Resources\Images\ImageResource;
use App\Models\Article;
use App\Models\Image;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditImage extends EditRecord
{
    protected static string $resource = ImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('goToOwner')
                ->label('Đi đến Owner')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->visible(fn (Image $record) => $record->model_type && $record->model_id)
                ->url(fn (Image $record) => $this->getOwnerUrl($record), shouldOpenInNewTab: true),
            Action::make('removeFromOwner')
                ->label('Xóa khỏi Owner')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Xóa ảnh khỏi Owner')
                ->modalDescription(fn (Image $record) => "Xóa ảnh này khỏi " . class_basename($record->model_type) . " #{$record->model_id}. File ảnh gốc vẫn còn trong storage.")
                ->modalSubmitActionLabel('Xóa khỏi Owner')
                ->visible(fn (Image $record) => $record->model_type && $record->model_id)
                ->action(function (Image $record) {
                    $ownerType = class_basename($record->model_type);
                    $ownerId = $record->model_id;
                    
                    // Soft delete the image record (keeps file)
                    $record->delete();
                    
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Đã xóa')
                        ->body("Đã xóa ảnh khỏi {$ownerType} #{$ownerId}.")
                        ->send();
                    
                    // Redirect to images list
                    return redirect()->route('filament.admin.resources.images.index');
                }),
            DeleteAction::make()
                ->before(function (DeleteAction $action, Image $record) {
                    // Kiểm tra xem ảnh có đang được dùng không
                    if ($record->model_type && $record->model_id) {
                        $ownerType = class_basename($record->model_type);
                        
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Không thể xóa ảnh')
                            ->body("Ảnh này đang được sử dụng bởi {$ownerType} #{$record->model_id}. Vui lòng gỡ liên kết trước khi xóa.")
                            ->persistent()
                            ->send();
                        
                        // Cancel deletion
                        $action->cancel();
                    }
                }),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    
    protected function getOwnerUrl(Image $record): ?string
    {
        if (!$record->model_type || !$record->model_id) {
            return null;
        }
        
        return match($record->model_type) {
            Product::class => route('filament.admin.resources.products.edit', ['record' => $record->model_id]),
            Article::class => route('filament.admin.resources.articles.edit', ['record' => $record->model_id]),
            default => null,
        };
    }
}
