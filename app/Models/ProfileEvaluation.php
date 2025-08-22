<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_description_id',
        'total_score',
        'strengths',
        'areas_for_improvement',
        'raw_output',
        'errors',
        'usage',
        'llm_model',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'usage' => 'array',
            'total_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class);
    }

    public function impact(): HasOne
    {
        return $this->hasOne(ProfileEvaluationImpact::class);
    }

    public function skillsAndTraits(): HasOne
    {
        return $this->hasOne(ProfileEvaluationSkillsAndTraits::class);
    }

    public function alignmentWithJob(): HasOne
    {
        return $this->hasOne(ProfileEvaluationAlignmentWithJob::class);
    }

    public function specificChanges(): HasMany
    {
        return $this->hasMany(ProfileEvaluationSpecificChange::class);
    }
}
