<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'cendanaK2net@gmail.com'],
            [
                'name' => 'Kang Dedi',
                'email' => 'cendanaK2net@gmail.com',
                'password' => Hash::make('Qwerty123*'),
                'role' => UserRole::ADMIN,
            ]
        );
    }
}
