<?php

namespace App\Support\Catalog;

class AttributeIconResolver
{
    public static function resolve(?string $iconPath): array
    {
        if (! $iconPath) {
            return ['icon_url' => null, 'icon_name' => null];
        }

        if (
            str_starts_with($iconPath, 'http://') ||
            str_starts_with($iconPath, 'https://') ||
            str_starts_with($iconPath, '/')
        ) {
            return ['icon_url' => $iconPath, 'icon_name' => null];
        }

        $isFilePath = str_contains($iconPath, '/') || str_contains($iconPath, '.');

        if ($isFilePath) {
            return ['icon_url' => asset('storage/'.$iconPath), 'icon_name' => null];
        }

        return ['icon_url' => null, 'icon_name' => $iconPath];
    }
}
