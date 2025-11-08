<?php

namespace App\Filament\Resources\HomeComponents\Pages;

use App\Filament\Resources\HomeComponents\HomeComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHomeComponents extends ListRecords
{
    protected static string $resource = HomeComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tạo'),
        ];
    }
}
