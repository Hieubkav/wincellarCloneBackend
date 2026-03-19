<?php

namespace App\Models;

use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(SettingObserver::class)]

class Setting extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'logo_image_id',
        'favicon_image_id',
        'og_image_id',
        'product_watermark_image_id',
        'product_watermark_position',
        'product_watermark_size',
        'product_watermark_type',
        'product_watermark_text',
        'product_watermark_text_size',
        'product_watermark_text_position',
        'product_watermark_text_position_y',
        'product_watermark_text_opacity',
        'product_watermark_text_repeat',
        'site_name',
        'hotline',
        'address',
        'hours',
        'email',
        'google_map_embed',
        'footer_config',
        'contact_config',
        'product_contact_cta_config',
        'product_shopee_link_enabled',
        'product_mobile_main_image_height',
        'product_detail_rules',
        'meta_default_title',
        'meta_default_description',
        'meta_default_keywords',
        'site_tagline',
        'organization_legal_name',
        'organization_short_name',
        'primary_phone',
        'primary_email',
        'price_range',
        'social_links_schema',
        'default_meta_title_template',
        'default_og_title',
        'default_og_description',
        'indexing_enabled',
        'global_font_key',
        'home_font_key',
        'product_list_font_key',
        'product_detail_font_key',
        'article_list_font_key',
        'article_detail_font_key',
        'extra',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'extra' => 'array',
            'meta_default_keywords' => 'array',
            'social_links_schema' => 'array',
            'indexing_enabled' => 'boolean',
            'footer_config' => 'array',
            'contact_config' => 'array',
            'product_contact_cta_config' => 'array',
            'product_shopee_link_enabled' => 'boolean',
            'product_mobile_main_image_height' => 'integer',
            'product_detail_rules' => 'array',
        ];
    }

    public function logoImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'logo_image_id');
    }

    public function faviconImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'favicon_image_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'og_image_id');
    }

    public function productWatermarkImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'product_watermark_image_id');
    }
}
