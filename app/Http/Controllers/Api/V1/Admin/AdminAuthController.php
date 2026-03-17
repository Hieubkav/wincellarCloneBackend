<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\ErrorType;
use App\Models\AdminAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        if (! Schema::hasTable('admin_access_tokens')) {
            return ErrorResponse::make(
                ErrorType::INTERNAL_ERROR,
                'Thiếu bảng admin_access_tokens. Vui lòng chạy migrate trước khi đăng nhập admin.',
                null,
                503
            );
        }

        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $login = trim($validated['login']);

        $user = User::query()
            ->select(['id', 'name', 'email', 'password'])
            ->where('email', $login)
            ->orWhere('name', $login)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return ErrorResponse::make(
                ErrorType::UNAUTHORIZED,
                'Tên đăng nhập hoặc mật khẩu không đúng',
                null,
                401
            );
        }

        AdminAccessToken::query()
            ->where('user_id', $user->id)
            ->delete();

        $plainTextToken = Str::random(80);
        $expiresAt = now()->addDays(14);

        AdminAccessToken::create([
            'user_id' => $user->id,
            'name' => 'admin-panel',
            'token_hash' => hash('sha256', $plainTextToken),
            'last_used_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $plainTextToken,
                'expires_at' => $expiresAt->toIso8601String(),
                'user' => $this->transformUser($user),
            ],
            'message' => 'Đăng nhập admin thành công',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->attributes->get('adminAccessToken');

        return response()->json([
            'data' => [
                'user' => $this->transformUser($user),
                'expires_at' => $token?->expires_at?->toIso8601String(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('adminAccessToken');

        if ($token instanceof AdminAccessToken) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã đăng xuất admin',
        ]);
    }

    private function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
