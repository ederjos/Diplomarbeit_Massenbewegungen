<?php

namespace App\Services;

use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MeasurementImportService
{
    // Keep one upload bounded so parsing and the bulk insert remain predictable in memory and duration.
    private const int MAX_ROWS = 10000;

    /**
     * Import one semicolon-delimited measurement file into a project.
     *
     * @param  array{name: string, measurement_datetime: string}  $data
     */
    public function import(Project $project, array $data, UploadedFile $file): Measurement
    {
        $rows = $this->parseRows($file);

        try {
            return DB::transaction(function () use ($project, $data, $rows): Measurement {
                // Serialize imports for this project so point creation cannot race.
                $lockedProject = Project::query()
                    ->whereKey($project->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedProject->measurements()->where('name', $data['name'])->exists()) {
                    throw ValidationException::withMessages([
                        'name' => 'Dieser Messungsname existiert bereits in diesem Projekt.',
                    ]);
                }

                $measurement = $lockedProject->measurements()->create([
                    'name' => $data['name'],
                    'measurement_datetime' => Carbon::parse($data['measurement_datetime']),
                ]);

                // Load existing points in one query to avoid an N+1 lookup for every CSV row.
                $points = $lockedProject->points()
                    ->whereIn('name', array_column($rows, 'pointName'))
                    ->get()
                    ->keyBy('name');
                $now = now();
                $measurementValues = [];

                foreach ($rows as $row) {
                    $point = $points->get($row['pointName']);

                    if (! $point) {
                        $point = $lockedProject->points()->create([
                            'name' => $row['pointName'],
                            'is_visible' => true,
                            'projection_id' => null,
                        ]);
                        $points->put($point->name, $point);
                    }

                    $measurementValues[] = [
                        'x' => $row['x'],
                        'y' => $row['y'],
                        'z' => $row['z'],
                        'geom' => MeasurementValue::computeGeom($row['x'], $row['y'], $row['z']),
                        'point_id' => $point->id,
                        'measurement_id' => $measurement->id,
                        'addition_id' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // fillAndInsert keeps the bulk insert while applying the geometry cast to Magellan points.
                MeasurementValue::query()->fillAndInsert($measurementValues);

                return $measurement;
            });
        } catch (QueryException $exception) {
            // The database constraint is the final protection against concurrent measurement inserts.
            if ($this->isMeasurementNameUniqueViolation($exception)) {
                throw ValidationException::withMessages([
                    'name' => 'Dieser Messungsname existiert bereits in diesem Projekt.',
                ]);
            }

            // This can happen when another import creates the same new point between the lookup and insert.
            if ($this->isPointNameUniqueViolation($exception)) {
                throw ValidationException::withMessages([
                    'file' => 'Ein Punktname wurde gleichzeitig in diesem Projekt angelegt. Bitte importieren Sie die Datei erneut.',
                ]);
            }

            throw $exception;
        }
    }

    /**
     * @return array<int, array{pointName: string, x: float, y: float, z: float}>
     */
    private function parseRows(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $handle = $path === false ? false : fopen($path, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'Die CSV-Datei konnte nicht gelesen werden.',
            ]);
        }

        $rows = [];
        $pointNames = [];
        $lineNumber = 0;
        $readError = false;

        try {
            while (($columns = fgetcsv($handle, separator: ';')) !== false) {
                $lineNumber++;
                $columns = array_map(static fn (?string $value): string => trim($value ?? ''), $columns);

                if ($columns === [''] || count(array_filter($columns, static fn (string $value): bool => $value !== '')) === 0) {
                    continue;
                }

                if (count($columns) !== 4 || $columns[0] === '') {
                    $this->invalidRow($lineNumber, 'Erwartet werden genau vier Spalten: Punktname;X;Y;Z.');
                }

                if (isset($pointNames[$columns[0]])) {
                    $this->invalidRow($lineNumber, 'Ein Punktname darf in einer Datei nur einmal vorkommen.');
                }

                if (mb_strlen($columns[0]) > 255) {
                    $this->invalidRow($lineNumber, 'Der Punktname darf höchstens 255 Zeichen lang sein.');
                }

                foreach ([1 => 'x', 2 => 'y', 3 => 'z'] as $index => $coordinate) {
                    if (! is_numeric($columns[$index]) || ! is_finite((float) $columns[$index])) {
                        $this->invalidRow($lineNumber, "Die Koordinate {$coordinate} muss eine Zahl mit Dezimalpunkt sein.");
                    }
                }

                $pointNames[$columns[0]] = true;
                $rows[] = [
                    'pointName' => $columns[0],
                    'x' => (float) $columns[1],
                    'y' => (float) $columns[2],
                    'z' => (float) $columns[3],
                ];

                if (count($rows) > self::MAX_ROWS) {
                    $this->invalidRow($lineNumber, 'Die CSV-Datei darf höchstens 10.000 Messpunkte enthalten.');
                }
            }
            $readError = ! feof($handle);
        } finally {
            // Validation errors can leave the loop early, so the handle must be closed here.
            fclose($handle);
        }

        if ($readError) {
            throw ValidationException::withMessages([
                'file' => 'Die CSV-Datei konnte nicht vollständig gelesen werden.',
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'Die CSV-Datei enthält keine Messwerte.']);
        }

        return $rows;
    }

    private function invalidRow(int $lineNumber, string $message): never
    {
        throw ValidationException::withMessages(['file' => "Zeile {$lineNumber}: {$message}"]);
    }

    private function isMeasurementNameUniqueViolation(QueryException $exception): bool
    {
        return $exception->getCode() === '23505'
            && str_contains($exception->getMessage(), 'measurements_project_id_name_unique');
    }

    private function isPointNameUniqueViolation(QueryException $exception): bool
    {
        return $exception->getCode() === '23505'
            && str_contains($exception->getMessage(), 'points_project_id_name_unique');
    }
}
