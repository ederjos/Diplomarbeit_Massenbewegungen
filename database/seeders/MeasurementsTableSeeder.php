<?php

namespace Database\Seeders;

use App\Models\Measurement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MeasurementsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lines = file(database_path('seeders/csv/Zeitpunkte.csv'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $projectId = 1;
        $measurements = [];

        foreach ($lines as $line) {
            $data = str_getcsv($line, ';');

            // name, date
            if (count($data) < 2) {
                continue;
            }

            $measurements[] = [
                'name' => $data[0],
                'measurement_datetime' => Carbon::createFromFormat('d.m.Y', $data[1])->startOfDay(),
                'project_id' => $projectId,
            ];
        }

        if (count($measurements) > 0) {
            // fillAndInsert() instead of insert() to automatically set created_at and updated_at timestamps
            Measurement::fillAndInsert($measurements);
        }
    }
}
