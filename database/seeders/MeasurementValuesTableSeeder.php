<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MeasurementValuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = File::files(database_path('seeders/csv'));

        // put NM.csv first since it is the base measurement
        // the other measurements (FM1.csv, ..., FMn.csv) are already ordered correctly
        usort($files, function ($a, $b) {
            if ($a->getFilename() === 'NM.csv') {
                return -1;
            }
            if ($b->getFilename() === 'NM.csv') {
                return 1;
            }
            return strnatcmp($a->getFilename(), $b->getFilename());
        });

        $points = DB::table('points')->pluck('id', 'name');
        
        foreach ($files as $file) {
            if ($file->getFilename() === 'Zeitpunkte.csv') {
                continue;
            }

            $measurementName = $file->getFilenameWithoutExtension();
            $measurement = DB::table('measurements')->where('name', $measurementName)->first();

            if (!$measurement) {
                continue;
            }

            $handle = fopen($file->getPathname(), 'r');
            $values = [];
            while (($line = fgetcsv($handle, 0, ',')) !== FALSE) {
                $pointName = 'PP' . $line[0];
                $pointId = $points[$pointName] ?? null;

                if (!$pointId) {
                    continue;
                }

                // Skip if coordinates are missing or invalid
                // e.g. FM10.csv
                if (!isset($line[1]) || !isset($line[2]) || !isset($line[3]) || 
                    $line[1] === '' || $line[2] === '' || $line[3] === '') {
                    continue;
                }

                $values[] = [
                    'point_id' => $pointId,
                    'x' => $line[1],
                    'y' => $line[2],
                    'z' => $line[3],
                    'measurement_id' => $measurement->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                if (count($values) >= 1000) {
                    DB::table('measurement_values')->insert($values);
                    $values = [];
                }
            }
            fclose($handle);

            if (!empty($values)) {
                DB::table('measurement_values')->insert($values);
            }
        }
    }
}
