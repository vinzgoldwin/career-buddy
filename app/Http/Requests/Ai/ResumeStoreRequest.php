<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class ResumeStoreRequest extends FormRequest
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
            'location' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],

            // Education rules
            'educations' => ['array'],
            'educations.*.school' => ['nullable', 'string', 'max:255'],
            'educations.*.degree' => ['nullable', 'string', 'max:255'],
            'educations.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'educations.*.start_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'educations.*.end_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'educations.*.currently_studying' => ['boolean'],
            'educations.*.grade' => ['nullable', 'string', 'max:50'],
            'educations.*.activities' => ['nullable', 'string', 'max:1000'],

            // Experience rules
            'experiences' => ['array'],
            'experiences.*.title' => ['nullable', 'string', 'max:255'],
            'experiences.*.company' => ['nullable', 'string', 'max:255'],
            'experiences.*.location' => ['nullable', 'string', 'max:255'],
            'experiences.*.start_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'experiences.*.end_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'experiences.*.currently_working' => ['boolean'],
            'experiences.*.employment_type' => ['nullable', 'string', 'max:100'],
            'experiences.*.industry' => ['nullable', 'string', 'max:100'],
            'experiences.*.description' => ['nullable', 'string', 'max:5000'],

            // Licenses and certifications rules
            'licenses_and_certifications' => ['array'],
            'licenses_and_certifications.*.name' => ['nullable', 'string', 'max:255'],
            'licenses_and_certifications.*.issuing_organization' => ['nullable', 'string', 'max:255'],
            'licenses_and_certifications.*.issue_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'licenses_and_certifications.*.expiration_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'licenses_and_certifications.*.credential_id' => ['nullable', 'string', 'max:255'],
            'licenses_and_certifications.*.credential_url' => ['nullable', 'url', 'max:255'],

            // Projects rules
            'projects' => ['array'],
            'projects.*.name' => ['nullable', 'string', 'max:255'],
            'projects.*.description' => ['nullable', 'string', 'max:5000'],
            'projects.*.start_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'projects.*.end_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'projects.*.url' => ['nullable', 'url', 'max:255'],
            'projects.*.skills_used' => ['nullable', 'string', 'max:1000'],

            // Skills rules
            'skills' => ['array'],
            'skills.*.name' => ['nullable', 'string', 'max:100'],
            'skills.*.proficiency_level' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'educations.*.start_date.regex' => 'Start date must be in MM/YYYY format.',
            'educations.*.end_date.regex' => 'End date must be in MM/YYYY format.',
            'experiences.*.start_date.regex' => 'Start date must be in MM/YYYY format.',
            'experiences.*.end_date.regex' => 'End date must be in MM/YYYY format.',
            'licenses_and_certifications.*.issue_date.regex' => 'Issue date must be in MM/YYYY format.',
            'licenses_and_certifications.*.expiration_date.regex' => 'Expiration date must be in MM/YYYY format.',
            'projects.*.start_date.regex' => 'Start date must be in MM/YYYY format.',
            'projects.*.end_date.regex' => 'End date must be in MM/YYYY format.',
        ];
    }
}
