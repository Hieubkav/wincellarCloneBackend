<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class EditorialSpotlightTransformer extends AbstractArticleListTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        $itemsConfig = $this->ensureArray($config['articles'] ?? []);

        $entries = $this->buildArticleEntries($component, $itemsConfig, $resources);

        if (empty($entries)) {
            return null;
        }

        $config['articles'] = $entries;

        return $resources->payload($component, $config);
    }
}
