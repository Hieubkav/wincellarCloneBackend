<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class SpeedDialTransformer extends AbstractComponentTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        $items = $this->ensureArray($config['items'] ?? []);

        $transformedItems = array_map(function ($item) use ($component, $resources) {
            $iconType = $item['icon_type'] ?? 'home';
            $iconImageId = $this->toPositiveInt($item['icon_image_id'] ?? null);

            $iconUrl = null;
            if ($iconType === 'custom' && $iconImageId) {
                $image = $resources->image($component, $iconImageId);
                $iconUrl = $image?->canonical_url ?? $image?->absolute_url;
            }

            return [
                'icon_type' => $iconType,
                'icon_url' => $iconUrl,
                'label' => $item['label'] ?? '',
                'href' => $item['href'] ?? '',
                'target' => $item['target'] ?? '_self',
            ];
        }, $items);

        return $resources->payload($component, [
            'items' => $transformedItems,
        ]);
    }
}
