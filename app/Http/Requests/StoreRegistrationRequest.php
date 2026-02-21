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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
                Password::required()->min(16)->uncompromised(),
                'confirmed',
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
