<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
