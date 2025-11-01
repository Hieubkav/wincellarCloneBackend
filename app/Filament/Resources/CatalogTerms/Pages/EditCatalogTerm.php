<?php

namespace App\Filament\Resources\CatalogTerms\Pages;

use App\Filament\Resources\CatalogTerms\CatalogTermResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogTerm extends EditRecord
{
    protected static string $resource = CatalogTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
