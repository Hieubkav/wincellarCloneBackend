<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class HeroCarouselTransformer extends AbstractComponentTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        $slidesConfig = $this->ensureArray($config['slides'] ?? []);

        $slides = [];

        foreach ($slidesConfig as $slide) {
            if (!is_array($slide)) {
                continue;
            }

            $imageId = $this->toPositiveInt($slide['image_id'] ?? null);
            if (!$imageId) {
                continue;
            }

            $image = $resources->image($component, $imageId);
            if (!$image) {
                continue;
            }

            $slides[] = [
                'image' => $resources->mapImage($image, $slide['alt'] ?? null),
                'href' => $slide['href'] ?? null,
            ];
        }

        if (empty($slides)) {
            return null;
        }

        $config['slides'] = $slides;

        return $resources->payload($component, $config);
    }
}
