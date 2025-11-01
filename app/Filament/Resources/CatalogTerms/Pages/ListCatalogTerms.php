<?php

namespace App\Filament\Resources\CatalogTerms\Pages;

use App\Filament\Resources\CatalogTerms\CatalogTermResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatalogTerms extends ListRecords
{
    protected static string $resource = CatalogTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
