<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class CategoryGridTransformer extends AbstractComponentTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        $itemsConfig = $this->ensureArray($config['categories'] ?? []);

        $categories = [];

        foreach ($itemsConfig as $item) {
            if (!is_array($item)) {
                continue;
            }

            $imageId = $this->toPositiveInt($item['image_id'] ?? null);
            if ($imageId) {
                $image = $resources->image($component, $imageId);
                if (!$image) {
                    continue;
                }

                $item['image'] = $resources->mapImage($image, $item['alt'] ?? null);
            }

            unset($item['image_id']);

            if (!empty($item)) {
                $categories[] = $item;
            }
        }

        if (empty($categories)) {
            return null;
        }

        $config['categories'] = $categories;

        return $resources->payload($component, $config);
    }
}

