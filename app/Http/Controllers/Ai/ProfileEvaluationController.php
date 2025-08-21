<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\ProfileEvaluation;
use App\Services\Profile\ProfileJsonService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileEvaluationController extends Controller
{
    public function show(ProfileEvaluation $evaluation, ProfileJsonService $profileService)
    {
        $this->authorizeView($evaluation);

        $job = $evaluation->jobDescription;
        $profile = $profileService->buildForUser(Auth::user());

        $jobNormalized = $job ? [
            'title' => $job->title,
            'seniority' => $job->seniority,
            'company_name' => $job->company_name,
            'work_mode' => $job->work_mode,
            'location' => $job->location,
            'employment_type' => $job->employment_type,
            'summary' => $job->summary,
            'responsibilities' => $job->responsibilities ?? [],
            'requirements' => $job->requirements ?? [],
            'skills' => $job->skills ?? [],
            'years_experience_min' => $job->years_experience_min,
            'years_experience_max' => $job->years_experience_max,
        ] : null;

        return Inertia::render('ai/ProfileEvaluation', [
            'evaluation' => [
                'id' => $evaluation->id,
                'total_score' => $evaluation->total_score,
                'impact' => $evaluation->impact ?? [],
                'skills_and_traits' => $evaluation->skills_and_traits ?? [],
                'alignment_with_job' => $evaluation->alignment_with_job ?? [],
                'overall' => [
                    'recommendation' => $evaluation->overall_recommendation,
                    'improvements' => $evaluation->improvements,
                ],
                'specific_changes' => $evaluation->specific_changes ?? [],
            ],
            'job' => $jobNormalized,
            'profile' => $profile,
        ]);
    }

    private function authorizeView(ProfileEvaluation $evaluation): void
    {
        if ($evaluation->user_id && Auth::id() !== $evaluation->user_id) {
            abort(403);
        }
    }
}
