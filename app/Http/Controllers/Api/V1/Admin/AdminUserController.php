<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private function getOwnerEmail(): ?string
    {
        $value = env('ADMIN_OWNER_EMAIL');

        if (! is_string($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        return $value !== '' ? $value : null;
    }

    private function isOwner(User $user): bool
    {
        $ownerEmail = $this->getOwnerEmail();

        if (! $ownerEmail) {
            return false;
        }

        return strtolower($user->email) === $ownerEmail;
    }

    private function transformUser(User $user, ?string $ownerEmail = null): array
    {
        $ownerEmail ??= $this->getOwnerEmail();
        $isOwner = $ownerEmail ? strtolower($user->email) === $ownerEmail : false;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'is_owner' => $isOwner,
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);
        $search = $request->input('search');

        $query = User::query()->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $ownerEmail = $this->getOwnerEmail();
        $items = $paginated->getCollection()->map(function (User $user) use ($ownerEmail) {
            return $this->transformUser($user, $ownerEmail);
        })->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json(['data' => $this->transformUser($user)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'data' => ['id' => $user->id],
            'message' => 'Tạo người dùng thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($this->isOwner($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa tài khoản chủ shop',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật người dùng thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($this->isOwner($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tài khoản chủ shop',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa người dùng thành công',
        ]);
    }
}
