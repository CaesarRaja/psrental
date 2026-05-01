<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Console;
use App\Models\Food;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@psrent.com',
            'phone' => '081234567890',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create Demo Customer
        User::create([
            'name' => 'Ahmad Rizky',
            'email' => 'demo@psrent.com',
            'phone' => '081234567891',
            'password' => Hash::make('demo123'),
            'role' => 'customer',
        ]);

        // Create Consoles
        $consoles = [
            ['name' => 'PS4-1', 'type' => 'PS4'],
            ['name' => 'PS4-2', 'type' => 'PS4'],
            ['name' => 'PS4-3', 'type' => 'PS4'],
            ['name' => 'PS5-1', 'type' => 'PS5'],
            ['name' => 'PS5-2', 'type' => 'PS5'],
            ['name' => 'PS5-3', 'type' => 'PS5'],
            ['name' => 'VR-1', 'type' => 'VR'],
            ['name' => 'VR-2', 'type' => 'VR'],
        ];

        foreach ($consoles as $console) {
            Console::create(array_merge($console, ['status' => 'available']));
        }

        // Create Food Items
        $foods = [
            ['emoji' => '🍔', 'name' => 'Burger', 'category' => 'Makanan', 'price' => 25000, 'stock' => 50],
            ['emoji' => '🍕', 'name' => 'Pizza Slice', 'category' => 'Makanan', 'price' => 20000, 'stock' => 30],
            ['emoji' => '🍗', 'name' => 'Chicken Wings', 'category' => 'Makanan', 'price' => 30000, 'stock' => 40],
            ['emoji' => '🍟', 'name' => 'French Fries', 'category' => 'Snack', 'price' => 15000, 'stock' => 60],
            ['emoji' => '🥤', 'name' => 'Cola', 'category' => 'Minuman', 'price' => 8000, 'stock' => 100],
            ['emoji' => '🧃', 'name' => 'Jus Jeruk', 'category' => 'Minuman', 'price' => 12000, 'stock' => 80],
            ['emoji' => '☕', 'name' => 'Kopi', 'category' => 'Minuman', 'price' => 10000, 'stock' => 50],
            ['emoji' => '🍿', 'name' => 'Popcorn', 'category' => 'Snack', 'price' => 10000, 'stock' => 70],
            ['emoji' => '🍦', 'name' => 'Ice Cream', 'category' => 'Snack', 'price' => 8000, 'stock' => 40],
            ['emoji' => '', 'name' => 'Mie Goreng', 'category' => 'Makanan', 'price' => 18000, 'stock' => 35],
        ];

        foreach ($foods as $food) {
            Food::create($food);
        }
    }
}