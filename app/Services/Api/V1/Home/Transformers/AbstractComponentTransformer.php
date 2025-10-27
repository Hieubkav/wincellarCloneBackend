<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;

abstract class AbstractComponentTransformer
{
    protected function normalizeConfig(HomeComponent $component): array
    {
        return is_array($component->config) ? $component->config : [];
    }

    /**
     * @param mixed $value
     */
    protected function toPositiveInt($value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value) && ctype_digit($value)) {
            $intValue = (int) $value;

            return $intValue > 0 ? $intValue : null;
        }

        return null;
    }

    protected function ensureArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }
}
