<?php

namespace Database\Seeders;

use App\Models\Console;
use Illuminate\Database\Seeder;

class ConsoleSeeder extends Seeder
{
    public function run(): void
    {
        $consoles = [
            // PS4
            ['name' => 'PS4-1', 'type' => 'PS4', 'status' => 'available', 'price_per_hour' => 15000],
            ['name' => 'PS4-2', 'type' => 'PS4', 'status' => 'available', 'price_per_hour' => 15000],
            ['name' => 'PS4-3', 'type' => 'PS4', 'status' => 'available', 'price_per_hour' => 15000],
            ['name' => 'PS4-4', 'type' => 'PS4', 'status' => 'available', 'price_per_hour' => 15000],
            // PS5
            ['name' => 'PS5-1', 'type' => 'PS5', 'status' => 'available', 'price_per_hour' => 25000],
            ['name' => 'PS5-2', 'type' => 'PS5', 'status' => 'available', 'price_per_hour' => 25000],
            ['name' => 'PS5-3', 'type' => 'PS5', 'status' => 'available', 'price_per_hour' => 25000],
            ['name' => 'PS5-4', 'type' => 'PS5', 'status' => 'available', 'price_per_hour' => 25000],
            // VR
            ['name' => 'VR-1', 'type' => 'VR', 'status' => 'available', 'price_per_hour' => 35000],
            ['name' => 'VR-2', 'type' => 'VR', 'status' => 'available', 'price_per_hour' => 35000],
            ['name' => 'VR-3', 'type' => 'VR', 'status' => 'available', 'price_per_hour' => 35000],
        ];

        foreach ($consoles as $console) {
            Console::create($console);
        }
    }
}
