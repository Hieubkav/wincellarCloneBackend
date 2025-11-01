<?php

namespace App\Filament\Resources\HomeComponents\Pages;

use App\Filament\Resources\HomeComponents\HomeComponentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHomeComponent extends ViewRecord
{
    protected static string $resource = HomeComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
