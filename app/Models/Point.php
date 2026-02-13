<?php

namespace App\Models;

use Carbon\Carbon;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Point extends Model
{
    use HasFactory;

    protected $casts = [
        // Sometimes boolean fields can be stored as integers (0/1) in the database
        'is_visible' => 'boolean',
    ];

    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projection()
    {
        return $this->belongsTo(Projection::class);
    }

    public function yearlyMovementInCm()
    {
        $firstMv = $this->measurementValues()
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            ->select('measurement_values.*', 'measurements.measurement_datetime')
            ->first();

        $lastMv = $this->measurementValues()
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderByDesc('measurements.measurement_datetime')
            ->select('measurement_values.*', 'measurements.measurement_datetime')
            ->first();

        if (! $firstMv || ! $lastMv || ! $firstMv->geom || ! $lastMv->geom) {
            // Must all be set
            return null;
        }

        if ($firstMv->id == $lastMv->id) {
            // Only one measurement value, so we don't consider it
            return null;
        }

        // Join needs to parse as Eloquent cast is not applied
        $firstDt = Carbon::parse($firstMv->measurement_datetime);
        $lastDt = Carbon::parse($lastMv->measurement_datetime);

        /**
         * Gemini 2.5 Pro, 2026-02-13
         * "So, I'd rather have geodetic correctness. Transform the coordinates to lat, lon upon selecting them from the db. [...]"
         */
        $distanceResult = DB::query()
            ->select(
                // Returned as meters in EPSG:31254; in WGS84, it would be in degrees!
                ST::distance3D(
                    ST::transform($firstMv->geom, config('spatial.srids.default')),
                    ST::transform($lastMv->geom, config('spatial.srids.default'))
                )->as('distance')
            )->first();

        $distance = $distanceResult->distance ?? null;

        $timeDifferenceInYears = $firstDt->diffInSeconds($lastDt, true) / (365.25 * 24 * 3600);

        if ($timeDifferenceInYears <= 0) {
            // Avoid division by zero or negative time difference
            return 0.0;
        }

        // Return the movement in cm/year
        return $distance * 100 / $timeDifferenceInYears;
    }
}
