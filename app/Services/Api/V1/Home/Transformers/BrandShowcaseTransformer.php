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

            $imageId = $this->toPositiveInt($item['image_id'] ?? null);
            if (!$imageId) {
                continue;
            }

            $image = $resources->image($component, $imageId);
            if (!$image) {
                continue;
            }

            $entries[] = [
                'image' => $resources->mapImage($image, $item['alt'] ?? null),
                'href' => $item['href'] ?? null,
            ];
        }

        if (empty($entries)) {
            return null;
        }

        $config['brands'] = $entries;

        return $resources->payload($component, $config);
    }
}
