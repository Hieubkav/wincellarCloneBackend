<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class AdminAccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'token_hash',
        'last_used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $token): void {
            $token->clearAuthCache();
        });

        static::deleted(function (self $token): void {
            $token->clearAuthCache();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function cacheKey(): string
    {
        return self::cacheKeyForHash($this->token_hash);
    }

    public static function cacheKeyForHash(string $tokenHash): string
    {
        return "admin_access_token:{$tokenHash}";
    }

    public function clearAuthCache(): void
    {
        Cache::forget($this->cacheKey());
    }
}
