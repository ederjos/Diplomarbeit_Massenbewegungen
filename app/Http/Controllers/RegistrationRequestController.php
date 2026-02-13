<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
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
            'password' => Hash::make($request->validated('password')),
            'note' => $request->validated('note'),
        ]);

        return redirect()->route('login')->with('status', 'Ihre Registrierungsanfrage wurde gesendet. Ein Administrator wird diese überprüfen.');
    }
}
