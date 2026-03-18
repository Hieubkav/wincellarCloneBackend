<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contactConfig = $this->contact_config;
        if (is_array($contactConfig) && isset($contactConfig['cards']) && is_array($contactConfig['cards'])) {
            $contactConfig['cards'] = array_map(function ($card) {
                if (! is_array($card)) {
                    return $card;
                }

                $icon = $card['icon'] ?? null;
                if (is_string($icon) && $icon !== '') {
                    if (! str_starts_with($icon, 'http://') && ! str_starts_with($icon, 'https://') && ! str_starts_with($icon, '/')) {
                        $card['icon'] = \App\Support\Catalog\AttributeIconResolver::normalizeIconName($icon);
                    }
                }

                return $card;
            }, $contactConfig['cards']);
        }

        return [
            'id' => $this->id,
            'site_name' => $this->site_name,
            'hotline' => $this->hotline,
            'address' => $this->address,
            'hours' => $this->hours,
            'email' => $this->email,
            'google_map_embed' => $this->google_map_embed,
            'footer_config' => $this->footer_config,
            'contact_config' => $contactConfig,
            'product_contact_cta_config' => $this->product_contact_cta_config,

            // Logo and favicon URLs
            'logo_url' => $this->logoImage?->url ?? '/placeholder/logo.svg',
            'favicon_url' => $this->faviconImage?->url ?? '/placeholder/favicon.ico',
            'og_image_url' => $this->ogImage?->url,
            'product_watermark_url' => $this->productWatermarkImage?->url,
            'product_watermark_position' => $this->product_watermark_position,
            'product_watermark_size' => $this->product_watermark_size,
            'product_watermark_type' => $this->product_watermark_type ?? 'image',
            'product_watermark_text' => $this->product_watermark_text,
            'product_watermark_text_size' => $this->product_watermark_text_size ?? 'medium',
            'product_watermark_text_position' => $this->product_watermark_text_position ?? 'center',
            'product_watermark_text_position_y' => $this->product_watermark_text_position_y,
            'product_watermark_text_opacity' => $this->product_watermark_text_opacity ?? 50,
            'product_watermark_text_repeat' => (bool) ($this->product_watermark_text_repeat ?? false),
            'global_font_key' => $this->global_font_key,
            'home_font_key' => $this->home_font_key,
            'product_list_font_key' => $this->product_list_font_key,
            'product_detail_font_key' => $this->product_detail_font_key,
            'article_list_font_key' => $this->article_list_font_key,
            'article_detail_font_key' => $this->article_detail_font_key,

            // SEO meta defaults (for pages without custom meta)
            'meta_defaults' => [
                'title' => $this->meta_default_title,
                'description' => $this->meta_default_description,
                'keywords' => $this->meta_default_keywords,
            ],
            'site_tagline' => $this->site_tagline,
            'organization_legal_name' => $this->organization_legal_name,
            'organization_short_name' => $this->organization_short_name,
            'primary_phone' => $this->primary_phone,
            'primary_email' => $this->primary_email,
            'price_range' => $this->price_range,
            'social_links_schema' => $this->social_links_schema,
            'default_meta_title_template' => $this->default_meta_title_template,
            'default_og_title' => $this->default_og_title,
            'default_og_description' => $this->default_og_description,
            'indexing_enabled' => $this->indexing_enabled,

            // Extra settings (consistent structure - always returns array)
            'extra' => $this->extra ?? [],

            // HATEOAS links
            '_links' => [
                'self' => [
                    'href' => route('api.v1.settings.index'),
                    'method' => 'GET',
                ],
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => 'v1',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
