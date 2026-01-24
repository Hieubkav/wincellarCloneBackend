<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class FavouriteProductsTransformer extends AbstractProductListTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        
        // Support both 'products' and 'product_ids' keys for backward compatibility
        $itemsConfig = $this->ensureArray($config['products'] ?? $config['product_ids'] ?? []);

        $entries = $this->buildProductEntries($component, $itemsConfig, $resources);

        if (empty($entries)) {
            return null;
        }

        $config['products'] = $entries;
        // Remove product_ids to avoid confusion in frontend
        unset($config['product_ids']);

        return $resources->payload($component, $config);
    }
}
