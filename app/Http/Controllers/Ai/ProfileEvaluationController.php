<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LicenseAndCertification;
use App\Models\ProfileEvaluation;
use App\Models\Project;
use App\Services\Profile\ProfileJsonService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileEvaluationController extends Controller
{
    public function show(ProfileEvaluation $evaluation, ProfileJsonService $profileService)
    {
        $this->authorizeView($evaluation);

        // Load the related models
        $evaluation->load(['impact', 'skillsAndTraits', 'alignmentWithJob', 'specificChanges']);

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

        // Prepare the impact data
        $impactData = $evaluation->impact ? [
            'quantifying_impact' => [
                'score' => $evaluation->impact->quantifying_impact_score,
                'feedback' => $evaluation->impact->quantifying_impact_feedback,
            ],
            'focus_on_achievements' => [
                'score' => $evaluation->impact->focus_on_achievements_score,
                'feedback' => $evaluation->impact->focus_on_achievements_feedback,
            ],
            'writing_quality' => [
                'score' => $evaluation->impact->writing_quality_score,
                'feedback' => $evaluation->impact->writing_quality_feedback,
            ],
            'varied_industry_specific_verbs' => [
                'score' => $evaluation->impact->varied_industry_specific_verbs_score,
                'feedback' => $evaluation->impact->varied_industry_specific_verbs_feedback,
            ],
        ] : [];

        // Prepare the skills and traits data
        $skillsAndTraitsData = $evaluation->skillsAndTraits ? [
            'problem_solving' => [
                'score' => $evaluation->skillsAndTraits->problem_solving_score,
                'feedback' => $evaluation->skillsAndTraits->problem_solving_feedback,
            ],
            'communication_collaboration' => [
                'score' => $evaluation->skillsAndTraits->communication_collaboration_score,
                'feedback' => $evaluation->skillsAndTraits->communication_collaboration_feedback,
            ],
            'initiative_innovation' => [
                'score' => $evaluation->skillsAndTraits->initiative_innovation_score,
                'feedback' => $evaluation->skillsAndTraits->initiative_innovation_feedback,
            ],
            'leadership_teamwork' => [
                'score' => $evaluation->skillsAndTraits->leadership_teamwork_score,
                'feedback' => $evaluation->skillsAndTraits->leadership_teamwork_feedback,
            ],
        ] : [];

        // Prepare the alignment with job data
        $alignmentWithData = $evaluation->alignmentWithJob ? [
            'skills_match' => [
                'score' => $evaluation->alignmentWithJob->skills_match_score,
                'feedback' => $evaluation->alignmentWithJob->skills_match_feedback,
            ],
            'job_title_match' => [
                'score' => $evaluation->alignmentWithJob->job_title_match_score,
                'feedback' => $evaluation->alignmentWithJob->job_title_match_feedback,
            ],
            'responsibilities_qualifications' => [
                'score' => $evaluation->alignmentWithJob->responsibilities_qualifications_score,
                'feedback' => $evaluation->alignmentWithJob->responsibilities_qualifications_feedback,
            ],
            'industry_keywords_synonyms' => [
                'score' => $evaluation->alignmentWithJob->industry_keywords_synonyms_score,
                'feedback' => $evaluation->alignmentWithJob->industry_keywords_synonyms_feedback,
            ],
        ] : [];

        // Prepare the specific changes data
        $specificChangesData = $evaluation->specificChanges->map(function ($change) {
            $reference = match ($change->field) {
                'experiences' => Experience::find($change->entity_id)?->title,
                'projects' => Project::find($change->entity_id)?->name,
                'education' => Education::find($change->entity_id)?->school,
                'licenses_and_certifications' => LicenseAndCertification::find($change->entity_id)?->name,
                default => null,
            };

            return [
                'field' => $change->field,
                'entity_id' => $change->entity_id,
                'specific_field' => $change->specific_field,
                'reference' => $reference,
                'old_value' => $this->formatChangeValue($change->old_value),
                'new_value' => $this->formatChangeValue($change->new_value),
            ];
        })->toArray();

        return Inertia::render('ai/ProfileEvaluation', [
            'evaluation' => [
                'id' => $evaluation->id,
                'total_score' => $evaluation->total_score,
                'impact' => $impactData,
                'skills_and_traits' => $skillsAndTraitsData,
                'alignment_with_job' => $alignmentWithData,
                'overall' => [
                    'strengths' => $evaluation->strengths,
                    'area_for_improvement' => $evaluation->areas_for_improvement,
                ],
                'specific_changes' => $specificChangesData,
            ],
            'job' => $jobNormalized,
            'profile' => $profile,
        ]);
    }

    private function formatChangeValue($value): string
    {
        if (!is_string($value)) {
            return (string) $value;
        }

        if (str_contains($value, ',')) {
            return collect(explode(',', $value))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->implode(', ');
        }

        return trim($value);
    }


    private function authorizeView(ProfileEvaluation $evaluation): void
    {
        if ($evaluation->user_id && Auth::id() !== $evaluation->user_id) {
            abort(403);
        }
    }
}
