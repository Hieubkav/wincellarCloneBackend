<?php

namespace App\Http\Middleware;

use App\Http\Responses\ErrorResponse;
use App\Models\AdminAccessToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $auditEnabled = $request->boolean('audit');
        $authStart = $auditEnabled ? microtime(true) : null;
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
        $cacheKey = 'admin:auth:token:'.$tokenHash;
        $tokenData = Cache::get($cacheKey);

        if (is_array($tokenData)) {
            $expiresAt = $tokenData['expires_at'] ? Carbon::parse($tokenData['expires_at']) : null;
            if ($expiresAt && $expiresAt->isPast()) {
                AdminAccessToken::query()->whereKey($tokenData['id'])->delete();
                Cache::forget($cacheKey);
                $tokenData = null;
            }
        }

        if (! is_array($tokenData)) {
            $token = AdminAccessToken::query()
                ->select([
                    'admin_access_tokens.id',
                    'admin_access_tokens.user_id',
                    'admin_access_tokens.name',
                    'admin_access_tokens.token_hash',
                    'admin_access_tokens.last_used_at',
                    'admin_access_tokens.expires_at',
                    'users.id as auth_user_id',
                    'users.name as auth_user_name',
                    'users.email as auth_user_email',
                ])
                ->join('users', 'users.id', '=', 'admin_access_tokens.user_id')
                ->where('admin_access_tokens.token_hash', $tokenHash)
                ->first();

            if (! $token || $token->isExpired()) {
                if ($token instanceof AdminAccessToken && $token->isExpired()) {
                    $token->delete();
                }

                return ErrorResponse::make(
                    \App\Http\Responses\ErrorType::UNAUTHORIZED,
                    'Admin session is invalid or expired',
                    null,
                    401
                );
            }

            $tokenData = [
                'id' => $token->id,
                'user_id' => $token->user_id,
                'name' => $token->name,
                'token_hash' => $token->token_hash,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
                'auth_user_id' => $token->getAttribute('auth_user_id'),
                'auth_user_name' => $token->getAttribute('auth_user_name'),
                'auth_user_email' => $token->getAttribute('auth_user_email'),
            ];

            Cache::put($cacheKey, $tokenData, 30);
        }

        $user = new User;
        $user->forceFill([
            'id' => $tokenData['auth_user_id'],
            'name' => $tokenData['auth_user_name'],
            'email' => $tokenData['auth_user_email'],
        ]);
        $user->exists = true;

        $lastUsedAt = $tokenData['last_used_at'] ? Carbon::parse($tokenData['last_used_at']) : null;

        if ($lastUsedAt === null || $lastUsedAt->lt(now()->subHours(6))) {
            AdminAccessToken::query()
                ->whereKey($tokenData['id'])
                ->update(['last_used_at' => now()]);
            $tokenData['last_used_at'] = now()->toIso8601String();
            Cache::put($cacheKey, $tokenData, 30);
        }

        $tokenModel = new AdminAccessToken;
        $tokenModel->forceFill([
            'id' => $tokenData['id'],
            'user_id' => $tokenData['user_id'],
            'name' => $tokenData['name'],
            'token_hash' => $tokenData['token_hash'],
            'last_used_at' => $tokenData['last_used_at'] ? Carbon::parse($tokenData['last_used_at']) : null,
            'expires_at' => $tokenData['expires_at'] ? Carbon::parse($tokenData['expires_at']) : null,
        ]);
        $tokenModel->exists = true;
        $tokenModel->setRelation('user', $user);
        $request->attributes->set('adminAccessToken', $tokenModel);
        auth()->setUser($user);

        if ($auditEnabled && $authStart !== null) {
            $request->attributes->set('audit', [
                'auth_ms' => (int) round((microtime(true) - $authStart) * 1000),
            ]);
        }

        return $next($request);
    }

    private function extractBearerToken(Request $request): ?string
    {
        $token = $request->bearerToken();

        return is_string($token) && $token !== '' ? $token : null;
    }
}
