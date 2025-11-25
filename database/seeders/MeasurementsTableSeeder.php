<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeasurementsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('seeders/csv/Zeitpunkte.csv');
        $file = fopen($csvFile, 'r');

        $measurements = [];
        $id = 1;
        while (($line = fgetcsv($file, 0, ';')) !== FALSE) {
            $measurements[] = [
                'id' => $id++,
                'name' => $line[0],
                'measurement_datetime' => \Carbon\Carbon::createFromFormat('d.m.Y', $line[1])->startOfDay(),
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        fclose($file);

        DB::table('measurements')->insert($measurements);
    }
}
