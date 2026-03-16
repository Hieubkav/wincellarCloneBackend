<?php

namespace App\Http\Middleware;

use App\Http\Responses\ErrorResponse;
use App\Models\AdminAccessToken;
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
            ->with('user')
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        if (! $token || ! $token->user || $token->isExpired()) {
            if ($token && $token->isExpired()) {
                $token->delete();
            }

            return ErrorResponse::make(
                \App\Http\Responses\ErrorType::UNAUTHORIZED,
                'Admin session is invalid or expired',
                null,
                401
            );
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

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
