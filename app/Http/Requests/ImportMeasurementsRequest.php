<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ImportMeasurementsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'measurement_datetime' => ['required', 'date_format:Y-m-d\\TH:i'],
            // Validate the upload server-side; the browser's accept attribute is only a usability hint.
            'file' => ['required', File::types(['csv', 'txt'])->max(10 * 1024)],
        ];
    }
}
