<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncMeasurementsRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class MeasurementManagementController extends Controller
{
    // GET /projects/{project}/measurements/manage
    public function manage(Project $project): Response
    {
        abort(501, 'Not Implemented');
    }

    // PUT (or POST?) /projects/{project}/measurements
    public function sync(SyncMeasurementsRequest $request, Project $project): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }
}
