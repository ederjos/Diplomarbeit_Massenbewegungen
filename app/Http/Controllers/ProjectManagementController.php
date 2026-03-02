<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ProjectManagementController extends Controller
{
    // GET /projects/create
    public function create(): Response
    {
        abort(501, 'Not Implemented');
    }

    // POST /projects
    public function store(): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }

    // GET /projects/{project}/edit
    public function edit(): Response
    {
        abort(501, 'Not Implemented');
    }

    // PUT /projects/{project}
    public function update(): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }

    // DELETE /projects/{project}
    public function destroy(): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }
}
