<?php

namespace App\Services\Profile;

use App\Models\User;

class ProfileJsonService
{
    /**
     * Build a complete profile JSON-ready array for the given user.
     * Accepts a User model instance or a user ID.
     */
    public function buildForUser(User|int $user): array
    {
        $model = $user instanceof User
            ? $user
            : User::query()->with([
                'education',
                'experiences',
                'licensesAndCertifications',
                'projects',
                'skills',
            ])->findOrFail($user);

        // Ensure related data is loaded when a User instance is passed in.
        if (! $model->relationLoaded('education')) {
            $model->load(['education', 'experiences', 'licensesAndCertifications', 'projects', 'skills']);
        }

        return [
            'id' => $model->id,
            'name' => $model->name,
            'email' => $model->email,
            'phone' => $model->phone,
            'location' => $model->location,
            'website' => $model->website,
            'summary' => $model->summary,

            'educations' => $model->education->map(function ($education) {
                return [
                    'id' => $education->id,
                    'school' => $education->school,
                    'degree' => $education->degree,
                    'field_of_study' => $education->field_of_study,
                    'start_date' => $this->toMonthYear($education->start_date?->format('Y-m-d')),
                    'end_date' => $this->toMonthYear($education->end_date?->format('Y-m-d')),
                    'currently_studying' => $education->currently_studying,
                    'grade' => $education->grade,
                    'activities' => $education->activities,
                    'description' => $education->description,
                ];
            })->values()->all(),

            'experiences' => $model->experiences->map(function ($experience) {
                return [
                    'id' => $experience->id,
                    'title' => $experience->title,
                    'company' => $experience->company,
                    'location' => $experience->location,
                    'start_date' => $this->toMonthYear($experience->start_date?->format('Y-m-d')),
                    'end_date' => $this->toMonthYear($experience->end_date?->format('Y-m-d')),
                    'currently_working' => $experience->currently_working,
                    'employment_type_id' => $experience->employment_type_id,
                    'industry' => $experience->industry,
                    'description' => $experience->description,
                ];
            })->values()->all(),

            'licenses_and_certifications' => $model->licensesAndCertifications->map(function ($license) {
                return [
                    'id' => $license->id,
                    'name' => $license->name,
                    'issuing_organization' => $license->issuing_organization,
                    'issue_date' => $this->toMonthYear($license->issue_date?->format('Y-m-d')),
                    'expiration_date' => $this->toMonthYear($license->expiration_date?->format('Y-m-d')),
                    'credential_id' => $license->credential_id,
                    'credential_url' => $license->credential_url,
                    'description' => $license->description,
                ];
            })->values()->all(),

            'projects' => $model->projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'start_date' => $this->toMonthYear($project->start_date?->format('Y-m-d')),
                    'end_date' => $this->toMonthYear($project->end_date?->format('Y-m-d')),
                    'url' => $project->url,
                    'skills_used' => $project->skills_used,
                ];
            })->values()->all(),

            'skills' => $model->skills->map(function ($skill) {
                return [
                    'name' => $skill->name,
                    'proficiency_level' => $skill->proficiency_level,
                ];
            })->values()->all(),
        ];
    }

    private function toMonthYear(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if ($dateObj) {
            return $dateObj->format('m/Y');
        }

        return $date;
    }
}
