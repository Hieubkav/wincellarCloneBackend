<?php

namespace App\Models;

use App\Models\Concerns\HasMediaGallery;
use App\Observers\MenuBlockItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(MenuBlockItemObserver::class)]
class MenuBlockItem extends Model
{
    use HasFactory;
    use HasMediaGallery;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'menu_block_id',
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

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

