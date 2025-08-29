<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class SubmitInterviewAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'answer' => ['required', 'string', 'min:1', 'regex:/\\b\\w+\\b/'],
        ];
    }

    public function messages(): array
    {
        return [
            'answer.regex' => 'Your answer must contain at least one word.',
        ];
    }
}

