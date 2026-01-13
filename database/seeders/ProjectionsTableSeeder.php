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
        // For now, no projections exist

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('projections_id_seq', (SELECT MAX(id) FROM projections));");
        }
    }
}
