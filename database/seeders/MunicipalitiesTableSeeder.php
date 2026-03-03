<?php

namespace Database\Seeders;

use App\Models\Municipality;
use Illuminate\Database\Seeder;

class MunicipalitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For test data don't use inefficient eloquent models
        Municipality::fillAndInsert([
            'name' => 'St. Gallenkirch',
        ]);
    }
}
