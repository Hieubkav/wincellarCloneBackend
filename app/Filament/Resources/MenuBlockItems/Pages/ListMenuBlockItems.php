<?php

namespace App\Filament\Resources\MenuBlockItems\Pages;

use App\Filament\Resources\MenuBlockItems\MenuBlockItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuBlockItems extends ListRecords
{
    protected static string $resource = MenuBlockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tạo'),
        ];
    }
}
