<?php

namespace App\Http\Middleware;

use App\Http\Responses\ErrorResponse;
use App\Models\AdminAccessToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
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
            ->where('admin_access_tokens.token_hash', hash('sha256', $plainToken))
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

        $user = new User();
        $user->forceFill([
            'id' => $token->getAttribute('auth_user_id'),
            'name' => $token->getAttribute('auth_user_name'),
            'email' => $token->getAttribute('auth_user_email'),
        ]);
        $user->exists = true;

        if ($token->last_used_at === null || $token->last_used_at->lt(now()->subHours(6))) {
            AdminAccessToken::query()
                ->whereKey($token->id)
                ->update(['last_used_at' => now()]);
            $token->last_used_at = now();
        }

        $token->setRelation('user', $user);
        $request->attributes->set('adminAccessToken', $token);
        auth()->setUser($user);

        return $next($request);
    }

    private function extractBearerToken(Request $request): ?string
    {
        $token = $request->bearerToken();

        return is_string($token) && $token !== '' ? $token : null;
    }
}
