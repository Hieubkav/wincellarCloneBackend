<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class FavouriteProductsTransformer extends AbstractProductListTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        $itemsConfig = $this->ensureArray($config['products'] ?? []);

        $entries = $this->buildProductEntries($component, $itemsConfig, $resources);

        if (empty($entries)) {
            return null;
        }

        $config['products'] = $entries;

        return $resources->payload($component, $config);
    }
}
