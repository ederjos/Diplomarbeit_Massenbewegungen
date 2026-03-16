<?php

namespace App\Http\Requests;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\Rules\Password;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'max:255',
                Email::default()->rfcCompliant()->rules([
                    Rule::unique(User::class, 'email'),
                    Rule::unique(RegistrationRequest::class, 'email'),
                ]),
            ],
            'password' => ['confirmed',
                // long passwords are more secure than complex ones
                // https://pages.nist.gov/800-63-3/sp800-63b.html#appA
                // https://xkcd.com/936/
                Password::required()->min(16)->max(128)->uncompromised(),
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
