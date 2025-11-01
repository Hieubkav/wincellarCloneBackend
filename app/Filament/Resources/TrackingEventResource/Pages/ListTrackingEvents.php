<?php

namespace App\Filament\Resources\TrackingEventResource\Pages;

use App\Filament\Resources\TrackingEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrackingEvents extends ListRecords
{
    protected static string $resource = TrackingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for read-only
        ];
    }
}
