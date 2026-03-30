<?php

namespace App\Support\Media;

use Illuminate\Support\Str;

final class MediaSemanticRegistry
{
    public const PRODUCT = 'product';

    public const ARTICLE = 'article';

    public const SETTINGS_LOGO = 'settings-logo';

    public const SETTINGS_FAVICON = 'settings-favicon';

    public const SETTINGS_OG = 'settings-og';

    public const SETTINGS_WATERMARK = 'settings-watermark';

    public const HOME = 'home';

    public const SOCIAL = 'social';

    public const CONTENT = 'content';

    public const SHARED = 'shared';

    public const TERM = 'term';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            self::PRODUCT,
            self::ARTICLE,
            self::SETTINGS_LOGO,
            self::SETTINGS_FAVICON,
            self::SETTINGS_OG,
            self::SETTINGS_WATERMARK,
            self::HOME,
            self::SOCIAL,
            self::CONTENT,
            self::SHARED,
            self::TERM,
        ];
    }

    public static function normalize(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $normalized = Str::of($value)
            ->lower()
            ->replace('_', '-')
            ->toString();

        return in_array($normalized, self::all(), true) ? $normalized : null;
    }

    public static function fromModelType(?string $modelType): ?string
    {
        if (! $modelType) {
            return null;
        }

        return match ($modelType) {
            'App\\Models\\Product' => self::PRODUCT,
            'App\\Models\\Article' => self::ARTICLE,
            'App\\Models\\CatalogTerm' => self::TERM,
            default => null,
        };
    }

    public static function fromSettingField(string $field): ?string
    {
        return match ($field) {
            'logo_image_id' => self::SETTINGS_LOGO,
            'favicon_image_id' => self::SETTINGS_FAVICON,
            'og_image_id' => self::SETTINGS_OG,
            'product_watermark_image_id' => self::SETTINGS_WATERMARK,
            default => null,
        };
    }
}
