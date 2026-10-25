<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@swatch.org.ng')],
            [
                'name' => env('ADMIN_NAME', 'SWATCH Admin'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe@2026')),
            ],
        );

        $this->call([
            ProjectSeeder::class,
            PostSeeder::class,
        ]);
    }
}
