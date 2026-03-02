<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class MeasurementManagementController extends Controller
{
    // GET /projects/{project}/measurements/create
    public function create(): Response
    {
        abort(501, 'Not Implemented');
    }

    // POST /projects/{project}/measurements
    public function store(): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }

    // GET /projects/{project}/measurements/{measurement}/edit
    public function edit(): Response
    {
        abort(501, 'Not Implemented');
    }

    // PUT /projects/{project}/measurements/{measurement}
    public function update(): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }

    // DELETE /projects/{project}/measurements/{measurement}
    public function destroy(): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }
}
