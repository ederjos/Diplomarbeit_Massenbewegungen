<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;

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

        // Cache existing points to avoid repeated DB queries
        // We assume project_id = 1 for all points created here
        $points = DB::table('points')->pluck('id', 'name')->toArray();
        $projectId = 1;

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
                // Skip empty lines or lines with insufficient data
                if (count($line) < 4) {
                    continue;
                }

                $pointName = trim($line[0]);
                
                if ($pointName === '') {
                    continue;
                }

                // Create point if it doesn't exist
                if (!isset($points[$pointName])) {
                    $pointId = DB::table('points')->insertGetId([
                        'name' => $pointName,
                        'project_id' => $projectId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $points[$pointName] = $pointId;
                }

                $pointId = $points[$pointName];

                // Validate coordinates (indices 1, 2, 3)
                // Ignore comments or extra columns
                $x = $line[1];
                $y = $line[2];
                $z = $line[3];

                if (!is_numeric($x) || !is_numeric($y) || !is_numeric($z)) {
                    continue;
                }

                $values[] = [
                    'point_id' => $pointId,
                    'x' => $x,
                    'y' => $y,
                    'z' => $z,
                    'geom' => MagellanPoint::make($x, $y, $z, null, 31254),
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
