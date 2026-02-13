<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MunicipalitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For test data don't use inefficient eloquent models
        DB::table('municipalities')->insert([
            'id' => 1,
            'name' => 'St. Gallenkirch',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'municipalities_id_seq\', (SELECT MAX(id) FROM municipalities));');
        }
    }
}
