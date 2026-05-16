<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductFilterGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'route_prefix',
        'position',
        'active',
        'show_in_filters',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'int',
            'active' => 'bool',
            'show_in_filters' => 'bool',
        ];
    }

    public function presets(): HasMany
    {
        return $this->hasMany(ProductFilterPreset::class, 'group_id')
            ->orderBy('position')
            ->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
