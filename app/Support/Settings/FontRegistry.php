<?php

namespace App\Support\Settings;

final class FontRegistry
{
    public const DEFAULT_FONT_KEY = 'be-vietnam-pro';

    public const ALL = [
        'be-vietnam-pro',
        'inter',
        'roboto',
        'noto-sans',
        'nunito',
        'source-sans-3',
        'merriweather',
        'lora',
        'montserrat',
        'noto-serif',
    ];

    public static function all(): array
    {
        return self::ALL;
    }

    public static function isValid(?string $value): bool
    {
        return $value !== null && in_array($value, self::ALL, true);
    }
}
