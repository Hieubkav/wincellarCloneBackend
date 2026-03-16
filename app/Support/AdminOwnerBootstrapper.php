<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminOwnerBootstrapper
{
    public function bootstrap(): void
    {
        $email = env('ADMIN_OWNER_EMAIL');
        $name = env('ADMIN_OWNER_NAME');
        $password = env('ADMIN_OWNER_PASSWORD');

        if (! is_string($email) || ! is_string($name) || ! is_string($password)) {
            return;
        }

        $email = trim($email);
        $name = trim($name);
        $password = trim($password);

        if ($email === '' || $name === '' || $password === '') {
            return;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);

            return;
        }

        $updates = [];

        if ($user->name !== $name) {
            $updates['name'] = $name;
        }

        if (! Hash::check($password, $user->password)) {
            $updates['password'] = $password;
        }

        if ($updates !== []) {
            $user->fill($updates)->save();
        }
    }
}
