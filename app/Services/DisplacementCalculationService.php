<?php

namespace App\Services;

use App\Models\MeasurementValue;
use App\Models\Projection;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

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

        // Projection onto user-defined axis via scalar dot product
        $projectedDistance = null;
        if ($projection) {
            $projectedDistance = abs($dX * $projection->ax + $dY * $projection->ay);
        }

        return [
            'distance2d' => $distance2d * self::METERS_TO_CENTIMETERS,
            'distance3d' => $distance3d * self::METERS_TO_CENTIMETERS,
            'deltaHeight' => $dZ * self::METERS_TO_CENTIMETERS,
            'projectedDistance' => $projectedDistance ? $projectedDistance * self::METERS_TO_CENTIMETERS : null,
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

        $firstMvs = MeasurementValue::whereIn('point_id', $pointIds)
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            ->select('measurement_values.point_id', 'measurement_values.geom')
            ->get()
            // unique only after querying
            ->unique('point_id')
            ->keyBy('point_id');

        $lastMvs = MeasurementValue::whereIn('point_id', $pointIds)
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderByDesc('measurements.measurement_datetime')
            ->select('measurement_values.point_id', 'measurement_values.geom')
            ->get()
            ->unique('point_id')
            ->keyBy('point_id');

        // Attach to each point so the resource can use them
        foreach ($visiblePoints as $point) {
            $point->preloadedFirstMv = $firstMvs->get($point->id);
            $point->preloadedLastMv = $lastMvs->get($point->id);
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
