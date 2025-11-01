<?php

namespace App\Filament\Resources\MenuBlocks\Pages;

use App\Filament\Resources\MenuBlocks\MenuBlockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuBlocks extends ListRecords
{
    protected static string $resource = MenuBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
