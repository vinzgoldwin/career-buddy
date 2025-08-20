<?php

namespace App\Services;

use App\Models\User;

class ResumeBuilderService
{
    public function getPrefillData(User $user): array
    {
        $user->load(['education', 'experiences', 'licensesAndCertifications', 'projects', 'skills']);

        return [
            'name' => $user->name,
            'location' => $user->location,
            'email' => $user->email,
            'website' => $user->website,
            'summary' => $user->summary,
            'educations' => $user->education->map(function ($education) {
                return [
                    'id' => $education->id,
                    'school' => $education->school,
                    'degree' => $education->degree,
                    'field_of_study' => $education->field_of_study,
                    'start_date' => $this->convertDateToMonthYear($education->start_date ? $education->start_date->format('Y-m-d') : null),
                    'end_date' => $this->convertDateToMonthYear($education->end_date ? $education->end_date->format('Y-m-d') : null),
                    'currently_studying' => $education->currently_studying,
                    'grade' => $education->grade,
                    'activities' => $education->activities,
                ];
            })->toArray() ?: [['id' => null, 'school' => '', 'degree' => '', 'field_of_study' => '', 'start_date' => '', 'end_date' => '', 'currently_studying' => false, 'grade' => '', 'activities' => '']],

            'experiences' => $user->experiences->map(function ($experience) {
                return [
                    'id' => $experience->id,
                    'title' => $experience->title,
                    'company' => $experience->company,
                    'location' => $experience->location,
                    'start_date' => $this->convertDateToMonthYear($experience->start_date ? $experience->start_date->format('Y-m-d') : null),
                    'end_date' => $this->convertDateToMonthYear($experience->end_date ? $experience->end_date->format('Y-m-d') : null),
                    'currently_working' => $experience->currently_working,
                    'employment_type_id' => $experience->employment_type_id,
                    'industry' => $experience->industry,
                    'description' => $experience->description,
                ];
            })->toArray() ?: [['id' => null, 'title' => '', 'company' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'currently_working' => false, 'employment_type_id' => null, 'industry' => '', 'description' => '']],

            'licenses_and_certifications' => $user->licensesAndCertifications->map(function ($license) {
                return [
                    'id' => $license->id,
                    'name' => $license->name,
                    'issuing_organization' => $license->issuing_organization,
                    'issue_date' => $this->convertDateToMonthYear($license->issue_date ? $license->issue_date->format('Y-m-d') : null),
                    'expiration_date' => $this->convertDateToMonthYear($license->expiration_date ? $license->expiration_date->format('Y-m-d') : null),
                    'credential_id' => $license->credential_id,
                    'credential_url' => $license->credential_url,
                ];
            })->toArray() ?: [['id' => null, 'name' => '', 'issuing_organization' => '', 'issue_date' => '', 'expiration_date' => '', 'credential_id' => '', 'credential_url' => '']],

            'projects' => $user->projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'start_date' => $this->convertDateToMonthYear($project->start_date ? $project->start_date->format('Y-m-d') : null),
                    'end_date' => $this->convertDateToMonthYear($project->end_date ? $project->end_date->format('Y-m-d') : null),
                    'url' => $project->url,
                    'skills_used' => $project->skills_used,
                ];
            })->toArray() ?: [['id' => null, 'name' => '', 'description' => '', 'start_date' => '', 'end_date' => '', 'url' => '', 'skills_used' => '']],

            'skills' => $user->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'proficiency_level' => $skill->proficiency_level,
                ];
            })->toArray() ?: [['id' => null, 'name' => '', 'proficiency_level' => 3]],
        ];
    }

    public function save(User $user, array $validated): void
    {
        $user->update([
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
            'summary' => $validated['summary'] ?? null,
        ]);

        if (isset($validated['educations'])) {
            $educationIds = [];
            foreach ($validated['educations'] as $education) {
                $data = collect($education)->except('id')->filter(fn ($v) => $v !== null && $v !== '');
                if ($data->isNotEmpty()) {
                    $model = $user->education()->updateOrCreate(
                        ['id' => $education['id'] ?? null],
                        [
                            'school' => $education['school'] ?? null,
                            'degree' => $education['degree'] ?? null,
                            'field_of_study' => $education['field_of_study'] ?? null,
                            'start_date' => $this->convertMonthYearToDate($education['start_date'] ?? null),
                            'end_date' => $this->convertMonthYearToDate($education['end_date'] ?? null),
                            'currently_studying' => $education['currently_studying'] ?? false,
                            'grade' => $education['grade'] ?? null,
                            'activities' => $education['activities'] ?? null,
                        ]
                    );
                    $educationIds[] = $model->id;
                }
            }
            if (empty($educationIds)) {
                $user->education()->delete();
            } else {
                $user->education()->whereNotIn('id', $educationIds)->delete();
            }
        }

        if (isset($validated['experiences'])) {
            $experienceIds = [];
            foreach ($validated['experiences'] as $experience) {
                $data = collect($experience)->except('id')->filter(fn ($v) => $v !== null && $v !== '');
                if ($data->isNotEmpty()) {
                    $model = $user->experiences()->updateOrCreate(
                        ['id' => $experience['id'] ?? null],
                        [
                            'title' => $experience['title'] ?? null,
                            'company' => $experience['company'] ?? null,
                            'location' => $experience['location'] ?? null,
                            'start_date' => $this->convertMonthYearToDate($experience['start_date'] ?? null),
                            'end_date' => $this->convertMonthYearToDate($experience['end_date'] ?? null),
                            'currently_working' => $experience['currently_working'] ?? false,
                            'employment_type_id' => $experience['employment_type_id'] ?? null,
                            'industry' => $experience['industry'] ?? null,
                            'description' => $experience['description'] ?? null,
                        ]
                    );
                    $experienceIds[] = $model->id;
                }
            }
            if (empty($experienceIds)) {
                $user->experiences()->delete();
            } else {
                $user->experiences()->whereNotIn('id', $experienceIds)->delete();
            }
        }

        if (isset($validated['licenses_and_certifications'])) {
            $licenseIds = [];
            foreach ($validated['licenses_and_certifications'] as $license) {
                $data = collect($license)->except('id')->filter(fn ($v) => $v !== null && $v !== '');
                if ($data->isNotEmpty()) {
                    $model = $user->licensesAndCertifications()->updateOrCreate(
                        ['id' => $license['id'] ?? null],
                        [
                            'name' => $license['name'] ?? null,
                            'issuing_organization' => $license['issuing_organization'] ?? null,
                            'issue_date' => $this->convertMonthYearToDate($license['issue_date'] ?? null),
                            'expiration_date' => $this->convertMonthYearToDate($license['expiration_date'] ?? null),
                            'credential_id' => $license['credential_id'] ?? null,
                            'credential_url' => $license['credential_url'] ?? null,
                        ]
                    );
                    $licenseIds[] = $model->id;
                }
            }
            if (empty($licenseIds)) {
                $user->licensesAndCertifications()->delete();
            } else {
                $user->licensesAndCertifications()->whereNotIn('id', $licenseIds)->delete();
            }
        }

        if (isset($validated['projects'])) {
            $projectIds = [];
            foreach ($validated['projects'] as $project) {
                $data = collect($project)->except('id')->filter(fn ($v) => $v !== null && $v !== '');
                if ($data->isNotEmpty()) {
                    $model = $user->projects()->updateOrCreate(
                        ['id' => $project['id'] ?? null],
                        [
                            'name' => $project['name'] ?? null,
                            'description' => $project['description'] ?? null,
                            'start_date' => $this->convertMonthYearToDate($project['start_date'] ?? null),
                            'end_date' => $this->convertMonthYearToDate($project['end_date'] ?? null),
                            'url' => $project['url'] ?? null,
                            'skills_used' => $project['skills_used'] ?? null,
                        ]
                    );
                    $projectIds[] = $model->id;
                }
            }
            if (empty($projectIds)) {
                $user->projects()->delete();
            } else {
                $user->projects()->whereNotIn('id', $projectIds)->delete();
            }
        }

        if (isset($validated['skills'])) {
            $skillIds = [];
            foreach ($validated['skills'] as $skill) {
                $data = collect($skill)->except('id')->filter(fn ($v) => $v !== null && $v !== '');
                if ($data->isNotEmpty()) {
                    $model = $user->skills()->updateOrCreate(
                        ['id' => $skill['id'] ?? null],
                        [
                            'name' => $skill['name'] ?? null,
                            'proficiency_level' => $skill['proficiency_level'] ?? 3,
                        ]
                    );
                    $skillIds[] = $model->id;
                }
            }
            if (empty($skillIds)) {
                $user->skills()->delete();
            } else {
                $user->skills()->whereNotIn('id', $skillIds)->delete();
            }
        }
    }

    private function convertDateToMonthYear(?string $date): ?string
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

    private function convertMonthYearToDate(?string $monthYear): ?string
    {
        if (! $monthYear) {
            return null;
        }

        if (preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $monthYear)) {
            $parts = explode('/', $monthYear);

            return $parts[1].'-'.$parts[0].'-01';
        }

        return $monthYear;
    }
}
