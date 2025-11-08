<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Pages;

use App\Filament\Resources\CatalogAttributeGroups\CatalogAttributeGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogAttributeGroup extends EditRecord
{
    protected static string $resource = CatalogAttributeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
