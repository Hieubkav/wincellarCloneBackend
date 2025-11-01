<?php

namespace App\Filament\Resources\MenuBlocks\Pages;

use App\Filament\Resources\MenuBlocks\MenuBlockResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuBlock extends EditRecord
{
    protected static string $resource = MenuBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
