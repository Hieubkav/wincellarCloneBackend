<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class BrandShowcaseTransformer extends AbstractComponentTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        $itemsConfig = $this->ensureArray($config['brands'] ?? []);

        $entries = [];

        foreach ($itemsConfig as $item) {
            if (!is_array($item)) {
                continue;
            }

            $termId = $this->toPositiveInt($item['term_id'] ?? null);
            if (!$termId) {
                continue;
            }

            $term = $resources->term($component, $termId);
            if (!$term) {
                continue;
            }

            $entries[] = [
                'term' => $resources->mapTermSummary($term),
                'href' => $item['href'] ?? null,
                'badge' => $item['badge'] ?? null,
            ];
        }

        if (empty($entries)) {
            return null;
        }

        $config['brands'] = $entries;

        return $resources->payload($component, $config);
    }
}
