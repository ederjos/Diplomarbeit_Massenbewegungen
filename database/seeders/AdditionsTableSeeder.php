<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For now, no additions

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'additions_id_seq\', (SELECT MAX(id) FROM additions));');
        }
    }
}
