<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileEvaluationSkillsAndTraits extends Model
{
    use HasFactory;

    protected $table = 'profile_evaluation_skills';

    protected $fillable = [
        'profile_evaluation_id',
        'problem_solving_score',
        'problem_solving_feedback',
        'communication_collaboration_score',
        'communication_collaboration_feedback',
        'initiative_innovation_score',
        'initiative_innovation_feedback',
        'leadership_teamwork_score',
        'leadership_teamwork_feedback',
    ];

    protected $casts = [
        'problem_solving_score' => 'integer',
        'communication_collaboration_score' => 'integer',
        'initiative_innovation_score' => 'integer',
        'leadership_teamwork_score' => 'integer',
    ];

    public function profileEvaluation(): BelongsTo
    {
        return $this->belongsTo(ProfileEvaluation::class);
    }
}