<?php

namespace App\Filament\Resources\MenuBlockItems\Pages;

use App\Filament\Resources\MenuBlockItems\MenuBlockItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMenuBlockItem extends ViewRecord
{
    protected static string $resource = MenuBlockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
