<?php

namespace Database\Seeders;

use App\Models\Addition;
use Illuminate\Database\Seeder;

class AdditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For point 7, which is the only one with an addition
        Addition::fillAndInsert([
            'dx' => -0.39,
            'dy' => 0.16,
            'dz' => -0.16,
            'created_at' => '2025-09-17 15:15:00',
        ]);
    }
}
