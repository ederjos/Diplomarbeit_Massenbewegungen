<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncMeasurementsRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MeasurementManagementController extends Controller
{
    // GET /projects/{project}/measurements/manage
    public function manage(Project $project): Response
    {
        $measurements = $project->measurements()
            ->orderBy('measurement_datetime')
            ->get(['id', 'name', 'measurement_datetime as datetime']);

        return Inertia::render('measurements/Manage', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'measurements' => $measurements,
        ]);
    }

    // PUT /projects/{project}/measurements
    public function sync(SyncMeasurementsRequest $request, Project $project): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }
}
