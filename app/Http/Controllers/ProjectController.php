<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeasurementResource;
use App\Http\Resources\PointResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectShowResource;
use App\Http\Resources\UserResource;
use App\Models\MeasurementValue;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        /**
         * Gemini 3 Pro, 2026-01-01
         * "What comes into ProjectController.php to get all projects with their last measurement date and next measurement date based on period?"
         * "Update the controller of index to correctly and simply return the requested data. no errors should appear when working with timezones +2 or +1. just return the date in the same form as you get it from measurement_datetime without timezone: 2025-06-04 00:00:00"
         */
        $projects = Project::query()
            ->withLastAndNextMeasurementDate()
            ->get();

        return Inertia::render('Home', [
            // ->resolve() removes the 'data' wrapper that strictly JsonResource adds
            'projects' => ProjectResource::collection($projects)->resolve(),
        ]);
    }

    // Request to access the query string parameter 'comparison' for comparison measurement, e.g. /projects/1?comp=2
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

        /**
         * Claude Opus 4.6, 2026-02-10
         * "Please apply the attached projection calculations to the project controller [...]"
         */
        // Compute displacements between reference and comparison epoch
        $displacements = [];
        if ($referenceId && $comparisonId) {
            // Bulk-load geom values for both epochs to avoid N+1 queries
            // Returns a Collection
            $refValues = MeasurementValue::where('measurement_id', $referenceId)
                // only values whose point id is in the visible points
                ->whereIn('point_id', $visiblePoints->pluck('id'))
                // keyBy ->
                ->get()->keyBy('point_id');

            $compValues = MeasurementValue::where('measurement_id', $comparisonId)
                ->whereIn('point_id', $visiblePoints->pluck('id'))
                ->get()->keyBy('point_id');

            foreach ($visiblePoints as $point) {
                $refVal = $refValues->get($point->id);
                $compVal = $compValues->get($point->id);

                if (! $refVal || ! $compVal) {
                    // Will not be displayed anyway, we have to skip it
                    continue;
                }

                // differences in EPSG:31254 (meters)
                // Step 1 - "head minus tail"
                $dX = $compVal->geom->getX() - $refVal->geom->getX();
                $dY = $compVal->geom->getY() - $refVal->geom->getY();
                $dZ = $compVal->geom->getZ() - $refVal->geom->getZ();

                // Steps 2a and 2c
                $distance2d = sqrt($dX ** 2 + $dY ** 2);
                $distance3d = sqrt($dX ** 2 + $dY ** 2 + $dZ ** 2);

                // Step 2b 1
                // Projection to user-defined axis (dot product)
                $projectedDistance = null;
                if ($point->projection) {
                    // Make projection unsigned like 2D delta
                    $projectedDistance = abs($dX * $point->projection->ax + $dY * $point->projection->ay);
                }

                $displacements[$point->id] = [
                    // m -> cm
                    'distance2d' => $distance2d * 100,
                    'distance3d' => $distance3d * 100,
                    'projectedDistance' => $projectedDistance !== null ? $projectedDistance * 100 : null,
                    'deltaHeight' => $dZ * 100,
                ];
            }
        }

        $contactPersons = $project->users()
            // not all users workking on this project, only contact persons
            ->wherePivot('is_contact_person', true)
            ->with('role')
            ->orderBy('name')
            ->get();

        return Inertia::render('Project', [
            'project' => (new ProjectShowResource($project))->resolve(),
            'points' => PointResource::collection($visiblePoints)->resolve(),
            'measurements' => MeasurementResource::collection($project->measurements)->resolve(),
            'referenceId' => $referenceId,
            'comparisonId' => $comparisonId,
            'displacements' => $displacements,
            'contactPersons' => UserResource::collection($contactPersons)->resolve(),
        ]);
    }
}
