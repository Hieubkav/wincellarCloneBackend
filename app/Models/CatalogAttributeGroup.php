<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogAttributeGroup extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'filter_type',
        'is_filterable',
        'is_primary',
        'position',
        'display_config',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_filterable' => 'bool',
            'is_primary' => 'bool',
            'position' => 'int',
            'display_config' => 'array',
        ];
    }

    public function terms(): HasMany
    {
        return $this->hasMany(CatalogTerm::class, 'group_id')
            ->orderBy('position');
    }

    public function menuBlocks(): HasMany
    {
        return $this->hasMany(MenuBlock::class, 'attribute_group_id')
            ->orderBy('order');
    }
}
