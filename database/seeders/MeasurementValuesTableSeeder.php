<?php

namespace Database\Seeders;

use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\Point;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MeasurementValuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = File::files(database_path('seeders/csv'));

        $files = array_filter($files, fn ($file) => $file->getFilename() !== 'Zeitpunkte.csv');

        // NM.csv is the base measurement and therefore should be processed first
        usort($files, function ($a, $b) {
            $aName = $a->getFilename();
            $bName = $b->getFilename();

            if ($aName === 'NM.csv') {
                return -1;
            }
            if ($bName === 'NM.csv') {
                return 1;
            }

            return strnatcasecmp($aName, $bName);
        });

        $projectId = 1; // should already exist
        $points = Point::where('project_id', $projectId)->pluck('id', 'name')->toArray();
        $measurementValues = [];

        foreach ($files as $file) {
            $measurementName = $file->getFilenameWithoutExtension();
            $measurement = Measurement::where('name', $measurementName)->first();

            if (! $measurement) {
                continue;
            }

            $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $data = str_getcsv($line, ',');

                // name, x, y, z
                if (count($data) < 4) {
                    continue;
                }

                $pointName = $data[0];
                $x = $data[1];
                $y = $data[2];
                $z = $data[3];

                if (! is_numeric($x) || ! is_numeric($y) || ! is_numeric($z)) {
                    continue;
                }

                if (isset($points[$pointName])) {
                    $pointId = $points[$pointName];
                } else {
                    // create new Point if it doesn't exist
                    // this means that a separate seeder for Points isn't necessary
                    $pointId = Point::insertGetId([
                        'name' => $pointName,
                        'project_id' => $projectId,
                    ]);
                    $points[$pointName] = $pointId;
                }

                $measurementValues[] = [
                    'point_id' => $pointId,
                    'measurement_id' => $measurement->id,
                    'x' => $x,
                    'y' => $y,
                    'z' => $z,
                ];
            }
        }

        if (count($measurementValues) > 0) {
            MeasurementValue::insert($measurementValues);
        }
    }
}
