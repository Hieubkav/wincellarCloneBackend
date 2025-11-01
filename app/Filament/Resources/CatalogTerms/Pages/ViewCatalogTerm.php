<?php

namespace App\Filament\Resources\CatalogTerms\Pages;

use App\Filament\Resources\CatalogTerms\CatalogTermResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCatalogTerm extends ViewRecord
{
    protected static string $resource = CatalogTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
