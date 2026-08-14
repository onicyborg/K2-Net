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
            ['email' => 'admin@k2net.local'],
            [
                'name' => 'Kang Dedi',
                'email' => 'admin@k2net.local',
                'password' => Hash::make('admin123'),
                'role' => UserRole::ADMIN,
            ]
        );
    }
}
