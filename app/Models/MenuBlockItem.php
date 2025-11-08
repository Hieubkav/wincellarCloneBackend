<?php

namespace App\Models;

use App\Models\Concerns\HasMediaGallery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuBlockItem extends Model
{
    use HasFactory;
    use HasMediaGallery;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'menu_block_id',
        'term_id',
        'label',
        'href',
        'badge',
        'order',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'int',
            'active' => 'bool',
        ];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(MenuBlock::class, 'menu_block_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(CatalogTerm::class, 'term_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Lấy label hiển thị - ưu tiên label thủ công, fallback về term->name
     */
    public function displayLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return (string) optional($this->term)->name;
    }

    /**
     * Lấy href hiển thị - ưu tiên href thủ công, fallback về href từ term
     */
    public function displayHref(): ?string
    {
        // Mode 1: Href thủ công (tel:, mailto:, external link...)
        if ($this->href) {
            return $this->href;
        }

        // Mode 2: Auto href từ term (taxonomy filter)
        if ($this->term) {
            $group = $this->term->group;
            if ($group) {
                // Build href dạng: /san-pham?filter[group_code]=term-slug
                return '/san-pham?' . http_build_query([
                    'filter' => [
                        $group->code => $this->term->slug,
                    ],
                ]);
            }
        }

        return null;
    }
}

