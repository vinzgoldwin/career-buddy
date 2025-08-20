<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class ParseJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'raw' => ['required', 'string', 'min:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'raw.required' => 'Please paste the job description.',
            'raw.min' => 'The job description seems too short.',
        ];
    }
}
