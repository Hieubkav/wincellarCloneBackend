<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;

abstract class BaseResource extends Resource
{
    public static function getCreateButtonLabel(): string
    {
        return 'Tạo';
    }

    /**
     * Get row number column for tables
     * Usage: static::getRowNumberColumn()
     */
    public static function getRowNumberColumn(): TextColumn
    {
        return TextColumn::make('row_number')
            ->label('STT')
            ->rowIndex()
            ->alignCenter()
            ->width(60);
    }
}
