<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_frontend')
                ->label('Web')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn() => ProductResource::getFrontendUrl($this->record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
