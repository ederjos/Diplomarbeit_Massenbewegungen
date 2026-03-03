<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
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
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        abort(501, 'Not Implemented');
    }

    // GET /projects/{project}/edit
    public function edit(Project $project): Response
    {
        abort(501, 'Not Implemented');
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
