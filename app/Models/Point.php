<?php

namespace App\Models;

use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
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

    public function axisPoint(): ?array
    {
        $projection = $this->projection;

        // If no projection is set, we cannot calculate an axis
        if (! $projection) {
            return null;
        }

        $firstMv = $this->measurementValues()
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            ->select('measurement_values.geom')
            ->first();

        $lastMv = $this->measurementValues()
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderByDesc('measurements.measurement_datetime')
            ->select('measurement_values.geom')
            ->first();

        if (! $firstMv || ! $firstMv->geom || ! $lastMv || ! $lastMv->geom) {
            return null;
        }

        // Calculate the projection of the last point

        // Calculate the total displacement from first to last measurement (in EPSG:31254)
        $dX = $lastMv->geom->getX() - $firstMv->geom->getX();
        $dY = $lastMv->geom->getY() - $firstMv->geom->getY();

        // Project the displacement onto the axis
        // Step 1 - dot product
        $projectedDistance = $dX * $projection->ax + $dY * $projection->ay;

        // Create end point by moving along the axis direction by the projected distance
        // Step 2 - multiply axis with dot product
        $newDX = $projection->ax * $projectedDistance;
        $newDY = $projection->ay * $projectedDistance;

        // Step 3 - add to first point coordinates (virtual coordinates)
        $endX = $firstMv->geom->getX() + $newDX;
        $endY = $firstMv->geom->getY() + $newDY;

        // Create end point geometry with default SRID
        $endPoint = MagellanPoint::make($endX, $endY, null, null, config('spatial.srids.default'));

        // Transform both points to EPSG:4326 (WGS84 for Leaflet)
        // Needs POSGIS, therefore DB::query
        $result = DB::query()
            ->select([
                ST::x(ST::transform($firstMv->geom, config('spatial.srids.wgs84')))->as('start_lon'),
                ST::y(ST::transform($firstMv->geom, config('spatial.srids.wgs84')))->as('start_lat'),
                ST::x(ST::transform($endPoint, config('spatial.srids.wgs84')))->as('end_lon'),
                ST::y(ST::transform($endPoint, config('spatial.srids.wgs84')))->as('end_lat'),
            ])
            ->first();

        if (! $result) {
            return null;
        }

        return [
            'startLat' => $result->start_lat,
            'startLon' => $result->start_lon,
            'endLat' => $result->end_lat,
            'endLon' => $result->end_lon,
            'vectorLat' => $result->end_lat - $result->start_lat,
            'vectorLon' => $result->end_lon - $result->start_lon,
        ];
    }

    public function yearlyMovementInCm(): ?float
    {
        $firstMv = $this->measurementValues()
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            ->select('measurement_values.id', 'measurement_values.geom', 'measurements.measurement_datetime')
            ->first();

        $lastMv = $this->measurementValues()
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderByDesc('measurements.measurement_datetime')
            ->select('measurement_values.id', 'measurement_values.geom', 'measurements.measurement_datetime')
            ->first();

        if (! $firstMv || ! $lastMv || ! $firstMv->geom || ! $lastMv->geom) {
            // Must all be set
            return null;
        }

        if ($firstMv->id === $lastMv->id) {
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

        $timeDifferenceInYears = $firstDt->diffInYears($lastDt, true);

        if ($timeDifferenceInYears <= 0) {
            // Avoid division by zero or negative time difference
            return null;
        }

        // Return the movement in cm/year
        return $distance * 100 / $timeDifferenceInYears;
    }
}
