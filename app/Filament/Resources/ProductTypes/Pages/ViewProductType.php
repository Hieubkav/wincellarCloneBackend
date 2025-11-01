<?php

namespace App\Filament\Resources\ProductTypes\Pages;

use App\Filament\Resources\ProductTypes\ProductTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductType extends ViewRecord
{
    protected static string $resource = ProductTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
