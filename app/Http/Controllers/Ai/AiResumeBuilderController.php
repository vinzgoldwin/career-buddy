<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ResumeStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AiResumeBuilderController extends Controller
{
    /**
     * Display the AI Resume Builder page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('ai/AiResumeBuilder');
    }

    /**
     * Store the resume data.
     *
     * @return RedirectResponse
     */
    public function store(ResumeStoreRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();

        // Update user's basic information
        $user->update([
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
            'summary' => $validated['summary'] ?? null,
        ]);

        // Process educations
        if (isset($validated['educations'])) {
            foreach ($validated['educations'] as $education) {
                if (! empty(array_filter($education))) {
                    $user->education()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'school' => $education['school'] ?? null,
                            'degree' => $education['degree'] ?? null,
                        ],
                        [
                            'field_of_study' => $education['field_of_study'] ?? null,
                            'start_date' => $education['start_date'] ?? null,
                            'end_date' => $education['end_date'] ?? null,
                            'currently_studying' => $education['currently_studying'] ?? false,
                            'grade' => $education['grade'] ?? null,
                            'activities' => $education['activities'] ?? null,
                        ]
                    );
                }
            }
        }

        // Process experiences
        if (isset($validated['experiences'])) {
            foreach ($validated['experiences'] as $experience) {
                if (! empty(array_filter($experience))) {
                    $user->experiences()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'title' => $experience['title'] ?? null,
                            'company' => $experience['company'] ?? null,
                        ],
                        [
                            'location' => $experience['location'] ?? null,
                            'start_date' => $experience['start_date'] ?? null,
                            'end_date' => $experience['end_date'] ?? null,
                            'currently_working' => $experience['currently_working'] ?? false,
                            'employment_type' => $experience['employment_type'] ?? null,
                            'industry' => $experience['industry'] ?? null,
                            'description' => $experience['description'] ?? null,
                        ]
                    );
                }
            }
        }

        // Process licenses and certifications
        if (isset($validated['licenses_and_certifications'])) {
            foreach ($validated['licenses_and_certifications'] as $license) {
                if (! empty(array_filter($license))) {
                    $user->licensesAndCertifications()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'name' => $license['name'] ?? null,
                            'issuing_organization' => $license['issuing_organization'] ?? null,
                        ],
                        [
                            'issue_date' => $license['issue_date'] ?? null,
                            'expiration_date' => $license['expiration_date'] ?? null,
                            'credential_id' => $license['credential_id'] ?? null,
                            'credential_url' => $license['credential_url'] ?? null,
                        ]
                    );
                }
            }
        }

        // Process projects
        if (isset($validated['projects'])) {
            foreach ($validated['projects'] as $project) {
                if (! empty(array_filter($project))) {
                    $user->projects()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'name' => $project['name'] ?? null,
                        ],
                        [
                            'description' => $project['description'] ?? null,
                            'start_date' => $project['start_date'] ?? null,
                            'end_date' => $project['end_date'] ?? null,
                            'url' => $project['url'] ?? null,
                            'skills_used' => $project['skills_used'] ?? null,
                        ]
                    );
                }
            }
        }

        // Process skills
        if (isset($validated['skills'])) {
            foreach ($validated['skills'] as $skill) {
                if (! empty($skill['name'])) {
                    $user->skills()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'name' => $skill['name'],
                        ],
                        [
                            'proficiency_level' => $skill['proficiency_level'] ?? 3,
                        ]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Resume data saved successfully!');
    }
}
