<?php

namespace App\Models;

use App\Observers\HomeComponentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(HomeComponentObserver::class)]
class HomeComponent extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
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
            'order' => 'int',
            'active' => 'bool',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
