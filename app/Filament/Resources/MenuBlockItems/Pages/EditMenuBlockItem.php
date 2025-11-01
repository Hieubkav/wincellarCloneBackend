<?php

namespace App\Filament\Resources\MenuBlockItems\Pages;

use App\Filament\Resources\MenuBlockItems\MenuBlockItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuBlockItem extends EditRecord
{
    protected static string $resource = MenuBlockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
