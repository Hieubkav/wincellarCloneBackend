<?php

namespace App\Support\Media;

class MediaConfig
{
    public static function placeholder(string $key): ?string
    {
        $placeholders = config('media.placeholders', []);

        if (array_key_exists($key, $placeholders)) {
            return $placeholders[$key] ?: null;
        }

        return $placeholders['default'] ?? null;
    }
}
