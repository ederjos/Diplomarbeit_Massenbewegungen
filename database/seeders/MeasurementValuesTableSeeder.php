<?php

namespace Database\Seeders;

use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\Point;
use App\Models\Project;
use Illuminate\Database\Seeder;

class MeasurementValuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projectId = 1;
        $measurements = Measurement::where('project_id', $projectId)->orderBy('measurement_datetime')->pluck('id', 'name')->toArray();
        $points = Point::where('project_id', $projectId)->pluck('id', 'name')->toArray();
        $measurementValues = [];

        foreach ($measurements as $measurementName => $measurementId) {
            $lines = file(database_path("seeders/csv/{$measurementName}.csv"), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (! $lines) {
                continue;
            }

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
                    $pointId = Point::fillAndInsertGetId([
                        'name' => $pointName,
                        'project_id' => $projectId,
                    ]);
                    $points[$pointName] = $pointId;
                }

                $measurementValues[] = [
                    'x' => $x,
                    'y' => $y,
                    'z' => $z,
                    'geom' => MeasurementValue::computeGeom($x, $y, $z),
                    'point_id' => $pointId,
                    'measurement_id' => $measurementId,
                ];
            }
        }

        if (count($measurementValues) > 0) {
            // fillAndInsert() instead of insert() to automatically set created_at and updated_at timestamps
            MeasurementValue::fillAndInsert($measurementValues);
        }

        // Set the first measurement (NM) as the reference epoch for the project
        $firstMeasurementId = $measurements['NM'];

        if ($firstMeasurementId) {
            Project::where('id', $projectId)->update([
                'reference_measurement_id' => $firstMeasurementId,
            ]);
        }
    }
}
