<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

abstract class BaseResource extends Resource
{
    public static function getCreateButtonLabel(): string
    {
        return 'Tạo';
    }
}
