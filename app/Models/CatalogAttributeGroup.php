<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'input_type',
        'is_filterable',
        'position',
        'display_config',
        'icon_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_filterable' => 'bool',
            'position' => 'int',
            'display_config' => 'array',
        ];
    }

    public function terms(): HasMany
    {
        return $this->hasMany(CatalogTerm::class, 'group_id')
            ->orderBy('position');
    }

    public function productTypes(): BelongsToMany
    {
        return $this->belongsToMany(ProductType::class, 'catalog_attribute_group_product_type', 'group_id', 'type_id')
            ->withPivot('position')
            ->orderByPivot('position');
    }
}
