<?php

namespace App\Models;

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

    public function axisPoint(MeasurementValue $firstMv, MeasurementValue $lastMv): ?array
    {
        $projection = $this->projection;

        if (! $projection || ! $firstMv || ! $firstMv->geom || ! $lastMv || ! $lastMv->geom) {
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

        /**
         * Gemini 2.5 Pro, 2026-02-13
         * "So, I'd rather have geodetic correctness. Transform the coordinates to lat, lon upon selecting them from the db. [...]"
         * Rem.: This comment was copied from the (now deleted) method yearlyMovementInCm() as this code was inspired by the distance calculation there.
         */
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
            'vectorLat' => $result->end_lat - $result->start_lat,
            'vectorLon' => $result->end_lon - $result->start_lon,
        ];
    }
}
