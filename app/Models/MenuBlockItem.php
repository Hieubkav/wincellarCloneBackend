<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuBlockItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'menu_block_id',
        'term_id',
        'label',
        'href',
        'badge',
        'meta',
        'order',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
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

    public function displayLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return (string) optional($this->term)->name;
    }

    public function displayHref(): ?string
    {
        return $this->href;
    }
}

