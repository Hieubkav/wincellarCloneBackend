<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'anon_id',
        'ip_hash',
        'user_agent',
        'first_seen_at',
        'last_seen_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(VisitorSession::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }
}
