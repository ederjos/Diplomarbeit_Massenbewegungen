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
            'email' => [
                'required',
                Email::default()->rfcCompliant()->preventSpoofing()->rules([
                    Rule::unique(User::class, 'email'),
                    Rule::unique(RegistrationRequest::class, 'email'),
                ]),
                'max:255',
            ],
            'password' => [
                // long passwords are more secure than complex ones
                // https://www.nist.gov/cybersecurity/how-do-i-create-good-password#what-is-nist%E2%80%99s-guidance-for-passwords
                // https://xkcd.com/936/
                Password::required()->min(16)->uncompromised(),
                'confirmed',
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
