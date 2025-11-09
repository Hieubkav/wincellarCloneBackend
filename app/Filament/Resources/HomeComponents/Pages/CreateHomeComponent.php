<?php

namespace App\Filament\Resources\HomeComponents\Pages;

use App\Filament\Resources\HomeComponents\HomeComponentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomeComponent extends CreateRecord
{
    protected static string $resource = HomeComponentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure config is properly set
        if (!isset($data['config'])) {
            $data['config'] = [];
        }

        return $data;
    }
}
