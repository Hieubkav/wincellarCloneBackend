<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class EditorialSpotlightTransformer extends AbstractArticleListTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);

        // Support both 'articles' and 'article_ids' keys for backward compatibility
        $itemsConfig = $this->ensureArray($config['articles'] ?? $config['article_ids'] ?? []);

        $entries = $this->buildArticleEntries($component, $itemsConfig, $resources);

        if (empty($entries)) {
            return null;
        }

        $config['articles'] = $entries;
        // Remove article_ids to avoid confusion in frontend
        unset($config['article_ids']);

        return $resources->payload($component, $config);
    }
}
