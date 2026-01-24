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
        return [
            'id' => $this->id,
            'site_name' => $this->site_name,
            'hotline' => $this->hotline,
            'address' => $this->address,
            'hours' => $this->hours,
            'email' => $this->email,
            'google_map_embed' => $this->google_map_embed,
            'footer_config' => $this->footer_config,
            'contact_config' => $this->contact_config,

            // Logo and favicon URLs
            'logo_url' => $this->logoImage?->url ?? '/placeholder/logo.svg',
            'favicon_url' => $this->faviconImage?->url ?? '/placeholder/favicon.ico',
            'product_watermark_url' => $this->productWatermarkImage?->url,
            'product_watermark_position' => $this->product_watermark_position,
            'product_watermark_size' => $this->product_watermark_size,
            'product_watermark_type' => $this->product_watermark_type ?? 'image',
            'product_watermark_text' => $this->product_watermark_text,
            'product_watermark_text_size' => $this->product_watermark_text_size ?? 'medium',
            'product_watermark_text_position' => $this->product_watermark_text_position ?? 'center',
            'product_watermark_text_opacity' => $this->product_watermark_text_opacity ?? 50,

            // SEO meta defaults (for pages without custom meta)
            'meta_defaults' => [
                'title' => $this->meta_default_title,
                'description' => $this->meta_default_description,
                'keywords' => $this->meta_default_keywords,
            ],

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
