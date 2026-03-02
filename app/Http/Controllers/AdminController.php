<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveRegistrationRequest;
use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    // GET /admin
    public function index(): Response
    {
        return Inertia::render('Admin', [
            'registrationRequests' => RegistrationRequest::query()
                ->orderBy('created_at')
                ->get(['id', 'name', 'email', 'note', 'created_at as createdAt']),
            'roles' => Role::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    // POST /admin/registration-requests/{registrationRequest}
    public function approve(ApproveRegistrationRequest $request, RegistrationRequest $registrationRequest): RedirectResponse
    {
        User::create([
            'name' => $registrationRequest->name,
            'email' => $registrationRequest->email,
            'password' => $registrationRequest->password,
            'role_id' => $request->validated('role_id'),
        ]);

        $registrationRequest->delete();

        return redirect()->route('admin');
    }

    // DELETE /admin/registration-requests/{registrationRequest}
    public function reject(RegistrationRequest $registrationRequest): RedirectResponse
    {
        $registrationRequest->delete();

        return redirect()->route('admin');
    }
}
