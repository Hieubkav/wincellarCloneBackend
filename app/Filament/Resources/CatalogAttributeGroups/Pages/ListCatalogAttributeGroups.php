<?php

namespace App\Filament\Resources\CatalogAttributeGroups\Pages;

use App\Filament\Resources\CatalogAttributeGroups\CatalogAttributeGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatalogAttributeGroups extends ListRecords
{
    protected static string $resource = CatalogAttributeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
