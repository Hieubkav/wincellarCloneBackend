<?php

namespace App\Filament\Resources\UrlRedirects\Pages;

use App\Filament\Resources\UrlRedirects\UrlRedirectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUrlRedirect extends ViewRecord
{
    protected static string $resource = UrlRedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
