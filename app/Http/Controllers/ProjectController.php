<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeasurementResource;
use App\Http\Resources\PointResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectShowResource;
use App\Http\Resources\UserResource;
use App\Models\MeasurementValue;
use App\Models\Project;
use App\Models\Projection;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProjectController extends Controller
{
    const METERS_TO_CENTIMETERS = 100;

    public function index(): Response
    {
        /**
         * Gemini 3 Pro, 2026-01-01
         * "What comes into ProjectController.php to get all projects with their last measurement date and next measurement date based on period?"
         * "Update the controller of index to correctly and simply return the requested data. no errors should appear when working with timezones +2 or +1. just return the date in the same form as you get it from measurement_datetime without timezone: 2025-06-04 00:00:00"
         */
        $user = request()->user();

        $projects = $user->projects()
            // Only select necessary columns
            ->select(['projects.id', 'projects.name', 'projects.is_active', 'projects.period'])
            ->withLastAndNextMeasurementDate()
            ->get();

        $favoriteProjectIds = $user ?
            $user->projects()->wherePivot('is_favorite', true)->pluck('projects.id')->toArray()
            : [];
        // Set the static property in the Resource (help provided by Claude Opus 4.6)
        ProjectResource::$favoriteProjectIds = $favoriteProjectIds;

        return Inertia::render('Home', [
            // ->resolve() removes the 'data' wrapper that strictly JsonResource adds
            'projects' => ProjectResource::collection($projects)->resolve(),
        ]);
    }

    public function toggleFavorite(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        $existingAssociation = $project->users()->where('user_id', $user->id)->first();
        // Toggle the is_favorite value
        $currentValue = $existingAssociation->pivot->is_favorite;
        $project->users()->updateExistingPivot($user->id, ['is_favorite' => ! $currentValue]);

        // Create a new redirect response to the previous location
        return back();
    }

    public function show(Request $request, Project $project): Response
    {
        // Apply scope to get first/last measurement dates
        $project = Project::query()
            ->where('id', $project->id)
            ->withFirstAndLastMeasurementDate()
            ->firstOrFail();

        // One project
        // Eager load everything: makes sure relations are loaded and ensures faster access
        $project->load([
            'points' => fn ($q) => $q->orderBy('id'),
            'points.projection',
            'points.measurementValues' => fn ($q) => $q->withLatLonAndOrderedByDate(),
            'measurements' => fn ($q) => $q->orderBy('measurement_datetime'),
            'measurements.comments' => fn ($q) => $q->orderBy('created_at'),
            'measurements.comments.user',
            'measurements.comments.user.role',
            'clerk',
            'client',
            'municipality',
            'type',
        ]);

        // Compute reference and comparison measurement IDs

        $measurementIds = $project->measurements->pluck('id');

        $referenceParam = $request->query('reference');
        if ($referenceParam && is_numeric($referenceParam) && $measurementIds->contains((int) $referenceParam)) {
            // 1. If a reference query is provided, use it
            $referenceId = (int) $referenceParam;
        } else {
            $refFromProject = $project->reference_measurement_id;
            if ($refFromProject && $measurementIds->contains($refFromProject)) {
                // 2. Otherwise, use configured reference measurement
                $referenceId = $refFromProject;
            } elseif ($project->measurements->count() > 0) {
                // 3. Finally, fall back to first measurement
                $referenceId = $project->measurements->first()->id;
            } else {
                // 4. If no measurements -> has to be null
                $referenceId = null;
            }
        }

        // Comparison epoch from query param, defaults to last measurement
        $comparisonParam = $request->query('comparison');
        if ($comparisonParam && is_numeric($comparisonParam) && $measurementIds->contains((int) $comparisonParam)) {
            // id is valid, just convert it to int
            $comparisonId = (int) $comparisonParam;
        } else {
            // if id invalid -> take last measurement as default
            $comparisonId = $project->measurements->count() > 1
                ? $project->measurements->last()->id
                : null;
        }

        // Only include visible points
        $visiblePoints = $project->points->filter(fn ($p) => $p->is_visible)->values();

        // Preload first and last measurement values for axis calculations
        // this avoids N+1 queries in the PointResource
        $pointIds = $visiblePoints->pluck('id');

        /**
         * Claude Opus 4.6, 2026-02-21
         * "Please optimize the loading as realized previously in this chat for the axis inside the ProjectController."
         */
        $firstMvs = MeasurementValue::whereIn('point_id', $pointIds)
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            ->select('measurement_values.point_id', 'measurement_values.geom')
            ->get()
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

        // Compute initial map displacements for the selected reference+comparison pair
        $mapDisplacements = ($referenceId && $comparisonId)
            ? $this->computeDisplacementsForPair($visiblePoints, $referenceId, $comparisonId)
            : [];

        // All displacements relative to the first measurement (Nullmessung) — used by DisplacementChart
        $chartDisplacements = $this->computeAllDisplacements($visiblePoints, $project->measurements);

        $contactPersons = $project->users()
            // not all users working on this project, only contact persons
            ->wherePivot('is_contact_person', true)
            ->with('role')
            ->orderBy('name')
            // Only the columns UserResource needs
            ->get(['users.id', 'users.name', 'users.role_id']);

        return Inertia::render('Project', [
            'project' => (new ProjectShowResource($project))->resolve(),
            'points' => PointResource::collection($visiblePoints)->resolve(),
            'measurements' => MeasurementResource::collection($project->measurements)->resolve(),
            'referenceId' => $referenceId,
            'comparisonId' => $comparisonId,
            'mapDisplacements' => $mapDisplacements,
            'chartDisplacements' => $chartDisplacements,
            'contactPersons' => UserResource::collection($contactPersons)->resolve(),
        ]);
    }

    /**
     * Claude Opus 4.6, 2026-02-10
     * "Please apply the attached projection calculations to the project controller [...]"
     */
    /**
     * Claude Opus 4.6, 2026-02-27
     * "Aktualisiere die Berechnung der Verschiebungen im ProjectController, damit sie für alle Punkte und Messungen durchgeführt wird, ähnlich wie in DisplacementChart. Extrahiere die aktualisierte Berechnung in eine neue Methode."
     * (Simon)
     */
    /**
     * Claude Opus 4.6, 2026-02-28
     * "Erstelle eine neue Methode zur Berechnung der Verschiebungen zwischen einer Referenz- und Vergleichsepoche, basierend auf der vorhandenen Logik. Die Rückgabe sollte eine Liste von Punkt-IDs mit den jeweiligen Verschiebungen sein."
     * (Simon)
     */

    /**
     * JSON API: Compute displacements between a specific reference and comparison measurement pair.
     */
    public function displacementsForPair(Request $request, Project $project): JsonResponse
    {
        $referenceId = (int) $request->query('reference');
        $comparisonId = (int) $request->query('comparison');

        // Validate that both measurements belong to this project
        $projectMeasurementIds = $project->measurements()->pluck('id');
        if (! $projectMeasurementIds->contains($referenceId) || ! $projectMeasurementIds->contains($comparisonId)) {
            abort(404);
        }

        $visiblePoints = $project->points()
            ->where('is_visible', true)
            ->with('projection')
            ->orderBy('id')
            ->get();

        return response()->json(
            $this->computeDisplacementsForPair($visiblePoints, $referenceId, $comparisonId)
        );
    }

    /**
     * Compute displacements between two specific measurements for all visible points.
     */
    private function computeDisplacementsForPair(Collection $visiblePoints, int $referenceId, int $comparisonId): array
    {
        $allValues = MeasurementValue::whereIn('point_id', $visiblePoints->pluck('id'))
            ->whereIn('measurement_id', [$referenceId, $comparisonId])
            ->select(['point_id', 'measurement_id', 'geom'])
            ->get()
            ->groupBy('point_id')
            ->map(fn ($group) => $group->keyBy('measurement_id'));

        $projectionsByPoint = $visiblePoints
            ->filter(fn ($p) => $p->projection !== null)
            ->pluck('projection', 'id');

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
    private function computeAllDisplacements(Collection $visiblePoints, Collection $measurements): array
    {
        if ($measurements->isEmpty() || $visiblePoints->isEmpty()) {
            return [];
        }

        // Bulk-load all geom values for all visible points across all measurements
        $allValues = MeasurementValue::whereIn('point_id', $visiblePoints->pluck('id'))
            ->whereIn('measurement_id', $measurements->pluck('id'))
            ->select(['point_id', 'measurement_id', 'geom'])
            ->get()
            // Group by point_id, then key each group by measurement_id
            ->groupBy('point_id')
            ->map(fn ($group) => $group->keyBy('measurement_id'));

        // Index projections by point id for fast lookup
        $projectionsByPoint = $visiblePoints
            ->filter(fn ($p) => $p->projection !== null)
            ->pluck('projection', 'id');

        $displacements = [];

        foreach ($visiblePoints as $point) {
            $pointValues = $allValues->get($point->id);
            if (! $pointValues) {
                continue;
            }

            // Use the first available measurement for this point as reference (Nullmessung)
            $refVal = null;
            foreach ($measurements as $measurement) {
                $val = $pointValues->get($measurement->id);
                if ($val) {
                    $refVal = $val;
                    break;
                }
            }
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
    private function computeDisplacement(MagellanPoint $refGeom, MagellanPoint $compGeom, ?Projection $projection = null): array
    {
        // Differences in EPSG:31254 (meters)
        $dX = $compGeom->getX() - $refGeom->getX();
        $dY = $compGeom->getY() - $refGeom->getY();
        $dZ = $compGeom->getZ() - $refGeom->getZ();

        $distance2d = sqrt($dX ** 2 + $dY ** 2);
        $distance3d = sqrt($dX ** 2 + $dY ** 2 + $dZ ** 2);

        // Projection to user-defined axis (dot product)
        $projectedDistance = null;
        if ($projection) {
            $projectedDistance = abs($dX * $projection->ax + $dY * $projection->ay);
        }

        return [
            'distance2d' => $distance2d * self::METERS_TO_CENTIMETERS,
            'distance3d' => $distance3d * self::METERS_TO_CENTIMETERS,
            'deltaHeight' => $dZ * self::METERS_TO_CENTIMETERS,
            'projectedDistance' => $projectedDistance !== null ? $projectedDistance * self::METERS_TO_CENTIMETERS : null,
        ];
    }
}
