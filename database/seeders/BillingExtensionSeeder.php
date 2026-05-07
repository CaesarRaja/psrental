<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillingExtensionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\BillingExtension::create([
            'reservation_id' => 2, // Asumsikan ada reservation id 2
            'requested_duration' => 60,
            'status' => 'pending',
            'admin_notes' => null,
        ]);
    }
}
