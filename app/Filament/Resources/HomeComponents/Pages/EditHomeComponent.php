<?php

namespace App\Filament\Resources\HomeComponents\Pages;

use App\Filament\Resources\HomeComponents\HomeComponentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomeComponent extends EditRecord
{
    protected static string $resource = HomeComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
