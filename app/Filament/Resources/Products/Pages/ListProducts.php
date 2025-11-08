<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected ?string $heading = 'Các Sản Phẩm';

    protected ?string $subheading = 'Danh mục (collection/khuyến mãi tùy ý) • Loại SP (đặc tính: Vang đỏ, Sake...) • Thuộc tính (Brand/Origin/Grape...)';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tạo'),
        ];
    }
}
