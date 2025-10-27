<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedUsers();

        $this->call([
            LookupTableSeeder::class,
            ArticleSeeder::class,
            ProductSeeder::class,
            SiteConfigSeeder::class,
            HomeComponentSeeder::class,
        ]);
    }

    private function seedUsers(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@wincellar.vn'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
             ]
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@wincellar.vn'],
            [
                'name' => 'Content Staff',
                'password' => Hash::make('Staff@123'),
            ]
        );
    }
}
