<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'type_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'type_id');
    }

    public function attributeGroups(): BelongsToMany
    {
        return $this->belongsToMany(CatalogAttributeGroup::class, 'catalog_attribute_group_product_type', 'type_id', 'group_id')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
