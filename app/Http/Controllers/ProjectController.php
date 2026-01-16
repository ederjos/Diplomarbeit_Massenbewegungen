<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeasurementResource;
use App\Http\Resources\PointResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): \Inertia\Response
    {
        /* Prompt (Gemini 3 Pro)
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

    public function show(Project $project): Response
    {
        // Eager load everything: makes sure relations are loaded and ensures faster access
        $project->load([
            'points.measurementValues' => fn($q) => $q->withLatLonAndOrderedByDate(),
            'measurements' => fn($q) => $q->orderBy('measurement_datetime')
        ]);

        return Inertia::render('Project', [
            'project' => (new ProjectResource($project))->resolve(),
            'points' => PointResource::collection($project->points)->resolve(),
            'measurements' => MeasurementResource::collection($project->measurements)->resolve()
        ]);
    }
}
