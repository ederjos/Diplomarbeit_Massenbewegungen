<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeasurementResource;
use App\Http\Resources\PointResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectShowResource;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Services\DisplacementCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProjectController extends Controller
{
    // https://laravel.com/docs/12.x/container
    public function __construct(
        protected DisplacementCalculationService $displacementService
    ) {}

    public function index(Request $request): Response
    {
        /**
         * Gemini 3 Pro, 2026-01-01
         * "What comes into ProjectController.php to get all projects with their last measurement date and next measurement date based on period?"
         * "Update the controller of index to correctly and simply return the requested data. no errors should appear when working with timezones +2 or +1. just return the date in the same form as you get it from measurement_datetime without timezone: 2025-06-04 00:00:00"
         */
        $user = $request->user();

        $projects = $user->projects()
            // Only select necessary columns
            ->select(['projects.id', 'projects.name', 'projects.is_active', 'projects.measurement_interval'])
            ->withLastMeasurementDate()
            ->get();

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

    // provide the project image as a separate endpoint, so it can be easily used in an <img> tag without needing to base64-encode it
    public function image(Project $project): HttpResponse
    {
        if (! $project->image) {
            abort(404);
        }

        /**
         * Claude 4.6 Sonnet, 2026-03-03
         * "Please fix the image insert in the seeder and the display in ProjectController."
         */
        // PostgreSQL returns bytea columns as a PHP stream resource
        $imageData = is_resource($project->image)
            ? stream_get_contents($project->image)
            : $project->image;

        return response($imageData, 200, [
            'Content-Type' => $project->image_mime_type ?? 'image/jpeg',
        ]);
    }

    public function show(Request $request, Project $project): Response
    {
        // Apply scope to get first/last measurement dates before querying
        $project = Project::query()
            ->withFirstAndLastMeasurementDate()
            // findOrFail: find a model by its primary key
            ->findOrFail($project->id);

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
        ]);

        // Only include visible points
        $visiblePoints = $project->points->filter(fn ($p) => $p->is_visible)->values();

        $this->displacementService->preloadMeasurementValues($visiblePoints);
        // Replaces the function call in PointResource
        $this->displacementService->computeAxisVectors($visiblePoints);

        [$referenceId, $comparisonId] = $this->resolveMeasurements($request, $project);

        // Compute initial map displacements for the selected reference+comparison pair
        $mapDisplacements = ($referenceId && $comparisonId)
            ? $this->displacementService->computeForPair($visiblePoints, $referenceId, $comparisonId)
            : [];

        // All displacements relative to the first measurement (Nullmessung) — used by DisplacementChart
        $chartDisplacements = $this->displacementService->computeAll($visiblePoints, $project->measurements);

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

    private function resolveMeasurements(Request $request, Project $project): array
    {
        // Gets the measurement ids from the query string

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

        return [$referenceId, $comparisonId];
    }

    /**
     * Claude Opus 4.6, 2026-02-10
     * "Please apply the attached projection calculations to the project controller [...]"
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
            $this->displacementService->computeForPair($visiblePoints, $referenceId, $comparisonId)
        );
    }
}
