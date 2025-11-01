<?php

namespace App\Filament\Resources\SocialLinks\Pages;

use App\Filament\Resources\SocialLinks\SocialLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocialLink extends EditRecord
{
    protected static string $resource = SocialLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
