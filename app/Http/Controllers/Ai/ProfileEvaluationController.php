<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LicenseAndCertification;
use App\Models\ProfileEvaluation;
use App\Models\ProfileEvaluationSpecificChange;
use App\Models\Project;
use App\Models\Skill;
use App\Services\Profile\ProfileJsonService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileEvaluationController extends Controller
{
    public function index()
    {
        $evaluations = ProfileEvaluation::query()
            ->with(['jobDescription:id,title,summary'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10)
            ->through(function (ProfileEvaluation $e) {
                return [
                    'id' => $e->id,
                    'total_score' => $e->total_score,
                    'created_at' => optional($e->created_at)->toIso8601String(),
                    'job' => [
                        'title' => $e->jobDescription->title ?? null,
                        'summary' => $e->jobDescription->summary ?? null,
                    ],
                ];
            });

        return Inertia::render('ai/ProfileEvaluations', [
            'evaluations' => $evaluations,
        ]);
    }

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
                'id' => $change->id,
                'field' => $change->field,
                'entity_id' => $change->entity_id,
                'specific_field' => $change->specific_field,
                'reference' => $reference,
                'old_value' => $this->formatChangeValue($change->old_value),
                'new_value' => $this->formatChangeValue($change->new_value),
                'applied' => (bool) $change->applied_at,
                'applied_at' => $change->applied_at?->toIso8601String(),
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
        if (! is_string($value)) {
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

    public function applyChange(ProfileEvaluation $evaluation, ProfileEvaluationSpecificChange $change)
    {
        $this->authorizeView($evaluation);

        if ($change->profile_evaluation_id !== $evaluation->id) {
            abort(404);
        }

        match ($change->field) {
            'summary' => $this->applySummary($change->new_value),
            'skills' => $this->applySkills($change->new_value),
            'experiences' => $this->applyModelField(Experience::class, $change->entity_id, $change->specific_field, $change->new_value),
            'education' => $this->applyModelField(Education::class, $change->entity_id, $change->specific_field, $change->new_value),
            'projects' => $this->applyModelField(Project::class, $change->entity_id, $change->specific_field, $change->new_value),
            'licenses_and_certifications' => $this->applyModelField(LicenseAndCertification::class, $change->entity_id, $change->specific_field, $change->new_value),
            default => null,
        };

        $change->update(['applied_at' => now()]);

        return back()->with('success', 'Change applied');
    }

    public function applyAll(ProfileEvaluation $evaluation)
    {
        $this->authorizeView($evaluation);

        $evaluation->load('specificChanges');

        foreach ($evaluation->specificChanges as $change) {
            match ($change->field) {
                'summary' => $this->applySummary($change->new_value),
                'skills' => $this->applySkills($change->new_value),
                'experiences' => $this->applyModelField(Experience::class, $change->entity_id, $change->specific_field, $change->new_value),
                'education' => $this->applyModelField(Education::class, $change->entity_id, $change->specific_field, $change->new_value),
                'projects' => $this->applyModelField(Project::class, $change->entity_id, $change->specific_field, $change->new_value),
                'licenses_and_certifications' => $this->applyModelField(LicenseAndCertification::class, $change->entity_id, $change->specific_field, $change->new_value),
                default => null,
            };

            if (! $change->applied_at) {
                $change->update(['applied_at' => now()]);
            }
        }

        return back()->with('success', 'All applicable changes applied');
    }

    private function applySummary(?string $value): void
    {
        Auth::user()?->update([
            'summary' => $this->formatChangeValue($value),
        ]);
    }

    private function applySkills(?string $value): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $formatted = $this->formatChangeValue($value);
        $skills = collect(explode(',', (string) $formatted))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->unique(function ($s) {
                return mb_strtolower($s);
            });

        foreach ($skills as $name) {
            $exists = Skill::query()
                ->where('user_id', $user->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->exists();

            if (! $exists) {
                $user->skills()->create([
                    'name' => $name,
                    'proficiency_level' => 3,
                ]);
            }
        }
    }

    private function applyModelField(string $modelClass, ?int $id, string $field, $value): void
    {
        if (! $id) {
            return;
        }

        $model = $modelClass::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $model) {
            return;
        }

        $safeFields = [
            Experience::class => ['title', 'company', 'location', 'description', 'industry'],
            Education::class => ['school', 'degree', 'field_of_study', 'description', 'grade', 'activities'],
            Project::class => ['name', 'description', 'url', 'skills_used'],
            LicenseAndCertification::class => ['name', 'issuing_organization', 'description', 'credential_id', 'credential_url'],
        ];

        $allowed = $safeFields[$modelClass] ?? [];
        if (! in_array($field, $allowed, true)) {
            return;
        }

        $formatted = $this->formatChangeValue($value);
        $payload = $formatted;
        if ($modelClass === Project::class && $field === 'skills_used') {
            $payload = collect(explode(',', (string) $formatted))
                ->map(fn ($s) => trim($s))
                ->filter()
                ->values()
                ->toArray();
        }

        $model->update([
            $field => $payload,
        ]);
    }
}
