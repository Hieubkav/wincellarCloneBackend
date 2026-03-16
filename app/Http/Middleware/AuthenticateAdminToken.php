<?php

namespace App\Http\Middleware;

use App\Http\Responses\ErrorResponse;
use App\Models\AdminAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $this->extractBearerToken($request);

        if ($plainToken === null) {
            return ErrorResponse::make(
                \App\Http\Responses\ErrorType::UNAUTHORIZED,
                'Admin authentication required',
                null,
                401
            );
        }

        $tokenHash = hash('sha256', $plainToken);
        $cacheKey = AdminAccessToken::cacheKeyForHash($tokenHash);

        $token = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($tokenHash) {
            return AdminAccessToken::query()
                ->with('user')
                ->where('token_hash', $tokenHash)
                ->first();
        });

        if (! $token || ! $token->user || $token->isExpired()) {
            if ($token instanceof AdminAccessToken && $token->isExpired()) {
                $token->delete();
            }

            Cache::forget($cacheKey);

            return ErrorResponse::make(
                \App\Http\Responses\ErrorType::UNAUTHORIZED,
                'Admin session is invalid or expired',
                null,
                401
            );
        }

        if ($token->last_used_at === null || $token->last_used_at->lt(now()->subMinutes(30))) {
            $token->forceFill([
                'last_used_at' => now(),
            ])->saveQuietly();
        }

        $request->attributes->set('adminAccessToken', $token);
        auth()->setUser($token->user);

        return $next($request);
    }

    private function extractBearerToken(Request $request): ?string
    {
        $token = $request->bearerToken();

        return is_string($token) && $token !== '' ? $token : null;
    }
}
