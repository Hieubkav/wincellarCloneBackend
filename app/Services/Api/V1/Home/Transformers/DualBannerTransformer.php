<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class DualBannerTransformer extends AbstractComponentTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        $bannersConfig = $this->ensureArray($config['banners'] ?? []);

        $banners = [];

        foreach ($bannersConfig as $banner) {
            if (!is_array($banner)) {
                continue;
            }

            $imageId = $this->toPositiveInt($banner['image_id'] ?? null);
            if ($imageId) {
                $image = $resources->image($component, $imageId);
                if (!$image) {
                    continue;
                }

                $banner['image'] = $resources->mapImage($image, $banner['alt'] ?? null);
            }

            unset($banner['image_id']);

            if (!empty($banner)) {
                $banners[] = $banner;
            }
        }

        if (empty($banners)) {
            return null;
        }

        $config['banners'] = $banners;

        return $resources->payload($component, $config);
    }
}
