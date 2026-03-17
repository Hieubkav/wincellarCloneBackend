<?php

namespace App\Support\Catalog;

use App\Models\Image;

class AttributeIconResolver
{
    public static function resolveFromGroup(?object $group): array
    {
        if (! $group) {
            return ['icon_url' => null, 'icon_name' => null];
        }

        $iconPath = $group->icon_path ?? null;
        if ($iconPath) {
            return self::resolve($iconPath);
        }

        $displayConfig = $group->display_config ?? null;
        if (is_string($displayConfig)) {
            $displayConfig = json_decode($displayConfig, true) ?? [];
        }

        $displayIcon = is_array($displayConfig) ? ($displayConfig['icon'] ?? null) : null;

        if (! $displayIcon) {
            return ['icon_url' => null, 'icon_name' => null];
        }

        return self::resolve($displayIcon);
    }

    public static function resolve(?string $iconPath): array
    {
        if (! $iconPath) {
            return ['icon_url' => null, 'icon_name' => null];
        }

        if (
            str_starts_with($iconPath, 'http://') ||
            str_starts_with($iconPath, 'https://')
        ) {
            return ['icon_url' => $iconPath, 'icon_name' => null];
        }

        if (str_starts_with($iconPath, '/storage/')) {
            $normalized = ltrim($iconPath, '/');
            return ['icon_url' => asset($normalized), 'icon_name' => null];
        }

        if (str_starts_with($iconPath, 'storage/')) {
            return ['icon_url' => asset($iconPath), 'icon_name' => null];
        }

        if (str_starts_with($iconPath, '/')) {
            return ['icon_url' => $iconPath, 'icon_name' => null];
        }

        $isFilePath = self::isFilePath($iconPath);

        if ($isFilePath) {
            $normalizedPath = str_starts_with($iconPath, 'storage/') ? $iconPath : 'storage/'.$iconPath;
            return ['icon_url' => asset($normalizedPath), 'icon_name' => null];
        }

        return ['icon_url' => null, 'icon_name' => self::normalizeIconName($iconPath)];
    }

    public static function isFilePath(string $iconPath): bool
    {
        return str_contains($iconPath, '/') || str_contains($iconPath, '.');
    }

    public static function normalizeIconName(string $iconPath): string
    {
        $normalizedPath = trim($iconPath);
        if (str_contains($normalizedPath, ':')) {
            $segments = explode(':', $normalizedPath);
            $normalizedPath = end($segments) ?: $normalizedPath;
        }

        $parts = preg_split('/[-_\s]+/', $normalizedPath) ?: [];
        $normalized = array_map(static fn ($part) => $part !== '' ? ucfirst($part) : '', $parts);

        return implode('', $normalized);
    }

    public static function resolveFromTerm(?string $iconType, ?string $iconValue): array
    {
        if (! $iconType || ! $iconValue) {
            return ['icon_url' => null, 'icon_name' => null];
        }

        if ($iconType === 'lucide') {
            return ['icon_url' => null, 'icon_name' => self::normalizeIconName($iconValue)];
        }

        if ($iconType === 'image') {
            if (is_numeric($iconValue)) {
                $image = Image::find((int) $iconValue);

                return ['icon_url' => $image?->absolute_url, 'icon_name' => null];
            }

            return self::resolve($iconValue);
        }

        return ['icon_url' => null, 'icon_name' => null];
    }
}
