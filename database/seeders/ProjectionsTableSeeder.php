<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hardcoded projection for point 5
        DB::table('projections')->insert([
            'id' => 1,
            'ax' => -0.678058168395027,
            'ay' => 0.735008245037279,
        ]);

        // Update point 5 to use the new projection
        DB::table('points')->where('id', 5)->update(['projection_id' => 1]);

        // update autoincrement value (this doesn't happen automatically when inserting with an id)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'projections_id_seq\', (SELECT MAX(id) FROM projections));');
        }
    }
}
