<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Hanya akun admin untuk login panel admin.
     * Email: admin@gmail.com | Password: admin12345
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'phone' => '081234567890',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
            ]
        );
    }
}
