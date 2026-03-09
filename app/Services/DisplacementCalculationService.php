<?php

namespace App\Services;

use App\Models\MeasurementValue;
use App\Models\Projection;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class DisplacementCalculationService
{
    const int METERS_TO_CENTIMETERS = 100;

    /**
     * Claude Opus 4.6, 2026-02-27
     * "Aktualisiere die Berechnung der Verschiebungen im ProjectController, damit sie für alle Punkte und Messungen durchgeführt wird, ähnlich wie in DisplacementChart. Extrahiere die aktualisierte Berechnung in eine neue Methode."
     * (Simon)
     * Rem by Josef: Some changes still in app\Http\Controllers\ProjectController.php
     */
    /**
     * Claude Opus 4.6, 2026-02-28
     * "Erstelle eine neue Methode zur Berechnung der Verschiebungen zwischen einer Referenz- und Vergleichsepoche, basierend auf der vorhandenen Logik. Die Rückgabe sollte eine Liste von Punkt-IDs mit den jeweiligen Verschiebungen sein."
     * (Simon)
     */

    /**
     * Compute displacements between two specific measurements for all visible points.
     */
    public function computeForPair(Collection $visiblePoints, int $referenceId, int $comparisonId): array
    {
        $allValues = $this->loadValues($visiblePoints, [$referenceId, $comparisonId]);

        $projectionsByPoint = $this->groupProjections($visiblePoints);

        $displacements = [];

        foreach ($visiblePoints as $point) {
            $pointValues = $allValues->get($point->id);
            if (! $pointValues) {
                continue;
            }

            $refVal = $pointValues->get($referenceId);
            $compVal = $pointValues->get($comparisonId);
            if (! $refVal || ! $compVal) {
                continue;
            }

            $displacements[$point->id] = $this->computeDisplacement(
                $refVal->geom,
                $compVal->geom,
                $projectionsByPoint->get($point->id),
            );
        }

        return $displacements;
    }

    /**
     * Compute displacements for every point × every measurement relative to the first measurement (Nullmessung).
     * Structure: pointId => { measurementId => { distance2d, distance3d, projectedDistance, deltaHeight } }
     */
    public function computeAll(Collection $visiblePoints, Collection $measurements): array
    {
        if ($measurements->isEmpty() || $visiblePoints->isEmpty()) {
            return [];
        }

        // Bulk-load all geom values for all visible points across all measurements
        $allValues = $this->loadValues($visiblePoints, $measurements->pluck('id')->all());

        // Index projections by point id for fast lookup
        $projectionsByPoint = $this->groupProjections($visiblePoints);

        $displacements = [];

        foreach ($visiblePoints as $point) {
            $pointValues = $allValues->get($point->id);
            if (! $pointValues) {
                continue;
            }

            // Use the first measurement with data as the reference (Nullmessung)
            // for every measurement get its pointValues and take the first with existing data
            $refVal = $measurements->map(fn ($m) => $pointValues->get($m->id))->first(fn ($v) => $v !== null);

            if (! $refVal) {
                continue;
            }

            $projection = $projectionsByPoint->get($point->id);
            $pointDisplacements = [];

            foreach ($measurements as $measurement) {
                $compVal = $pointValues->get($measurement->id);
                if (! $compVal) {
                    continue;
                }

                $pointDisplacements[$measurement->id] = $this->computeDisplacement(
                    $refVal->geom,
                    $compVal->geom,
                    $projection,
                );
            }

            if (! empty($pointDisplacements)) {
                $displacements[$point->id] = $pointDisplacements;
            }
        }

        return $displacements;
    }

    /**
     * Compute displacement metrics between two geometry points.
     */
    public function computeDisplacement(MagellanPoint $refGeom, MagellanPoint $compGeom, ?Projection $projection = null): array
    {
        // Differences in EPSG:31254 (meters)
        $dX = $compGeom->getX() - $refGeom->getX();
        $dY = $compGeom->getY() - $refGeom->getY();
        $dZ = $compGeom->getZ() - $refGeom->getZ();

        $distance2d = sqrt($dX ** 2 + $dY ** 2);
        $distance3d = sqrt($dX ** 2 + $dY ** 2 + $dZ ** 2);

        // Projection onto user-defined axis via scalar dot product done in Projection Model

        return [
            'distance2d' => $distance2d * self::METERS_TO_CENTIMETERS,
            'distance3d' => $distance3d * self::METERS_TO_CENTIMETERS,
            'deltaHeight' => $dZ * self::METERS_TO_CENTIMETERS,
            'projectedDistance' => $projection ? abs($projection->projectDisplacement($dX, $dY)) * self::METERS_TO_CENTIMETERS : null,
        ];
    }

    public function preloadMeasurementValues(Collection $visiblePoints): void
    {
        /**
         * Claude Opus 4.6, 2026-02-21
         * "Please optimize the loading as realized previously in this chat for the axis inside the ProjectController."
         */

        // Preload first and last measurement values for axis calculations
        // this avoids N+1 queries in the PointResource
        $pointIds = $visiblePoints->pluck('id');

        $allMvs = MeasurementValue::whereIn('point_id', $pointIds)
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            ->select('measurement_values.point_id', 'measurement_values.geom')
            ->get()
            // group by the point ids -> for every point id we want to access first and last measurement values
            ->groupBy('point_id');

        // Attach to each point so the resource can use them
        foreach ($visiblePoints as $point) {
            $mvs = $allMvs->get($point->id);
            // Not really less efficient than doing unique after getting
            $point->preloadedFirstMv = $mvs->first();
            $point->preloadedLastMv = $mvs->last();
        }
    }

    /**
     * Compute the projected axis vector for every visible point using their preloaded
     * first/last MeasurementValues. Stamps `preloadedAxis` onto each Point model.
     *
     * Must be called after preloadMeasurementValues().
     */
    public function computeAxisVectors(Collection $visiblePoints): void
    {
        foreach ($visiblePoints as $point) {
            $firstMv = $point->preloadedFirstMv ?? null;
            $lastMv = $point->preloadedLastMv ?? null;
            $projection = $point->projection;

            if (! $projection || ! $firstMv?->geom || ! $lastMv?->geom) {
                $point->preloadedAxis = null;

                continue;
            }

            // Calculate the total displacement from first to last measurement (in EPSG:31254)
            $dX = $lastMv->geom->getX() - $firstMv->geom->getX();
            $dY = $lastMv->geom->getY() - $firstMv->geom->getY();

            // Project the displacement onto the axis
            // Step 1 - dot product
            $projectedDistance = $projection->projectDisplacement($dX, $dY);

            // Create end point by moving along the axis direction by the projected distance
            // Step 2 - multiply axis with dot product & Step 3 - add to first point coordinates (virtual coordinates)
            $endX = $firstMv->geom->getX() + $projection->ax * $projectedDistance;
            $endY = $firstMv->geom->getY() + $projection->ay * $projectedDistance;

            // Create end point geometry with default SRID
            $endPoint = MagellanPoint::make($endX, $endY, null, null, config('spatial.srids.default'));

            // Transform both points to EPSG:4326 (WGS84 for Leaflet)
            // Needs POSTGIS, therefore DB::query

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

            $point->preloadedAxis = $result ? [
                'startLat' => $result->start_lat,
                'startLon' => $result->start_lon,
                'vectorLat' => $result->end_lat - $result->start_lat,
                'vectorLon' => $result->end_lon - $result->start_lon,
            ] : null;
        }
    }

    // Helper functions to reduce redundant code
    private function loadValues(Collection $visiblePoints, array $measurementIds): SupportCollection
    {
        return MeasurementValue::whereIn('point_id', $visiblePoints->pluck('id'))
            ->whereIn('measurement_id', $measurementIds)
            ->select(['point_id', 'measurement_id', 'geom'])
            ->get()
            // Group by point_id, then key each group by measurement_id
            ->groupBy('point_id')
            ->map(fn ($group) => $group->keyBy('measurement_id'));
    }

    private function groupProjections(Collection $visiblePoints): SupportCollection
    {
        return $visiblePoints
            ->filter(fn ($p) => $p->projection !== null)
            ->pluck('projection', 'id');
    }
}
