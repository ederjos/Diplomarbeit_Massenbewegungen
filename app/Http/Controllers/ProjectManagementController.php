<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectManagementController extends Controller
{
    // GET /projects/create
    public function create(): Response
    {
        return Inertia::render('projects/Create');
    }

    // POST /projects
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }

    // GET /projects/{project}/edit
    public function edit(Project $project): Response
    {
        $measurements = $project->measurements()
            ->orderBy('measurement_datetime')
            ->get(['id', 'name', 'measurement_datetime as datetime']);

        return Inertia::render('projects/Edit', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'isActive' => $project->is_active,
                'comment' => $project->comment,
                'lastFileNumber' => $project->last_file_number,
                'measurementInterval' => $project->measurement_interval,
                'movementMagnitude' => $project->movement_magnitude,
                'client' => $project->client,
                'clerk' => $project->clerk,
                'municipality' => $project->municipality,
                'type' => $project->type,
                'referenceMeasurementId' => $project->reference_measurement_id,
            ],
            // needed for reference measurement selection
            'measurements' => $measurements,
        ]);
    }

    // PUT /projects/{project}
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }

    // DELETE /projects/{project}
    public function delete(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('home');
    }
}
