<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Pages;

use App\Filament\Resources\CatalogAttributeGroups\CatalogAttributeGroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCatalogAttributeGroup extends ViewRecord
{
    protected static string $resource = CatalogAttributeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
