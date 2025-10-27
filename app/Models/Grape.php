<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grape extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_grapes')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
