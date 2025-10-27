<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuBlock extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'menu_id',
        'title',
        'attribute_group_id',
        'max_terms',
        'config',
        'order',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'max_terms' => 'int',
            'order' => 'int',
            'active' => 'bool',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(CatalogAttributeGroup::class, 'attribute_group_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuBlockItem::class)
            ->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

