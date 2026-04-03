<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationRequestController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        RegistrationRequest::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'), // Hashing is done by Eloquent
            'note' => $request->validated('note'),
        ]);

        return redirect()->route('login')->with('status', 'Ihre Registrierungsanfrage wurde gesendet. Ein Administrator wird diese überprüfen.');
    }
}
