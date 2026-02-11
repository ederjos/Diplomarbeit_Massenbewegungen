<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeasurementResource;
use App\Http\Resources\PointResource;
use App\Http\Resources\ProjectResource;
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
        // One project
        // Eager load everything: makes sure relations are loaded and ensures faster access
        $project->load([
            'points' => fn ($q) => $q->orderBy('id'),
            'points.projection',
            'points.measurementValues' => fn ($q) => $q->withLatLonAndOrderedByDate(),
            'measurements' => fn ($q) => $q->orderBy('measurement_datetime'),
        ]);

        // Reference measurement returns first measurement if not yet configured
        $referenceId = $project->reference_measurement_id;
        if (! $referenceId && $project->measurements->count() > 0) {
            $referenceId = $project->measurements->first()->id;
        }

        /**
         * Claude Opus 4.6, 2026-02-11
         * "now, take a look at the entire #codebase. is this code ready to be commited? Can you still find some errors?"
         * Rem.: Before, I was using is_int which didn't work with query _string_ parameters
         */
        // Comparison epoch from query param, defaults to last measurement
        $comparisonId = $request->query('comparison');

        if ($comparisonId && is_numeric($comparisonId)) {
            $comparisonId = (int) $comparisonId;
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
            $refValues = MeasurementValue::where('measurement_id', $referenceId)
                ->whereIn('point_id', $visiblePoints->pluck('id'))
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
                $dX = $compVal->geom->getX() - $refVal->geom->getX();
                $dY = $compVal->geom->getY() - $refVal->geom->getY();
                $dZ = $compVal->geom->getZ() - $refVal->geom->getZ();

                $distance2d = sqrt($dX ** 2 + $dY ** 2);
                $distance3d = sqrt($dX ** 2 + $dY ** 2 + $dZ ** 2);

                // Projektion auf normierte Achse (Skalarprodukt)
                $projectedDistance = null;
                if ($point->projection) {
                    $projectedDistance = $dX * $point->projection->ax + $dY * $point->projection->ay;
                }

                $displacements[$point->id] = [
                    'distance2d' => $distance2d * 100, // m → cm
                    'distance3d' => $distance3d * 100,
                    'projectedDistance' => $projectedDistance !== null ? $projectedDistance * 100 : null,
                    'deltaHeight' => $dZ * 100,
                ];
            }
        }

        return Inertia::render('Project', [
            'project' => (new ProjectResource($project))->resolve(),
            'points' => PointResource::collection($visiblePoints)->resolve(),
            'measurements' => MeasurementResource::collection($project->measurements)->resolve(),
            'referenceId' => $referenceId,
            'comparisonId' => $comparisonId,
            'displacements' => $displacements,
        ]);
    }
}
