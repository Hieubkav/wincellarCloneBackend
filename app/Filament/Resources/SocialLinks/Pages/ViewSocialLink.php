<?php

namespace App\Filament\Resources\SocialLinks\Pages;

use App\Filament\Resources\SocialLinks\SocialLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSocialLink extends ViewRecord
{
    protected static string $resource = SocialLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
