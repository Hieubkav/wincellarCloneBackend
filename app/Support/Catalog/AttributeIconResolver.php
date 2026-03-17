<?php

namespace App\Support\Catalog;

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

        return ['icon_url' => null, 'icon_name' => self::normalizeIconName($displayIcon)];
    }

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

        $isFilePath = self::isFilePath($iconPath);

        if ($isFilePath) {
            return ['icon_url' => asset('storage/'.$iconPath), 'icon_name' => null];
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
}
