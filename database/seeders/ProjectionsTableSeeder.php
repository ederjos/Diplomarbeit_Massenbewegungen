<?php

namespace Database\Seeders;

use App\Models\Point;
use App\Models\Projection;
use Illuminate\Database\Seeder;

class ProjectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hardcoded projection for point 5
        Projection::fillAndInsert([
            'ax' => -0.678058168395027,
            'ay' => 0.735008245037279,
        ]);

        // Update point 5 to use the new projection
        Point::where('id', 5)->update(['projection_id' => 1]);
    }
}
