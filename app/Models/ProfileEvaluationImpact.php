<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileEvaluationImpact extends Model
{
    use HasFactory;

    protected $table = 'profile_evaluation_impacts';

    protected $fillable = [
        'profile_evaluation_id',
        'quantifying_impact_score',
        'quantifying_impact_feedback',
        'focus_on_achievements_score',
        'focus_on_achievements_feedback',
        'writing_quality_score',
        'writing_quality_feedback',
        'varied_industry_specific_verbs_score',
        'varied_industry_specific_verbs_feedback',
    ];

    protected $casts = [
        'quantifying_impact_score' => 'integer',
        'focus_on_achievements_score' => 'integer',
        'writing_quality_score' => 'integer',
        'varied_industry_specific_verbs_score' => 'integer',
    ];

    public function profileEvaluation(): BelongsTo
    {
        return $this->belongsTo(ProfileEvaluation::class);
    }
}