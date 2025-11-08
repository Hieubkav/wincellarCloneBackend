<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogTerm extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'group_id',
        'name',
        'slug',
        'description',
        'icon_type',
        'icon_value',
        'metadata',
        'is_active',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'bool',
            'position' => 'int',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CatalogAttributeGroup::class, 'group_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_term_assignments',
            'term_id',
            'product_id'
        )
            ->withPivot(['position', 'extra'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'term_id');
    }

    public function menuBlockItems(): HasMany
    {
        return $this->hasMany(MenuBlockItem::class, 'term_id');
    }
}
