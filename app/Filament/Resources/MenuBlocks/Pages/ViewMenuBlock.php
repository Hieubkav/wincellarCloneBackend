<?php

namespace App\Filament\Resources\MenuBlocks\Pages;

use App\Filament\Resources\MenuBlocks\MenuBlockResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMenuBlock extends ViewRecord
{
    protected static string $resource = MenuBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
