<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileEvaluationAlignmentWithJob extends Model
{
    use HasFactory;

    protected $table = 'profile_evaluation_alignments';

    protected $fillable = [
        'profile_evaluation_id',
        'skills_match_score',
        'skills_match_feedback',
        'job_title_match_score',
        'job_title_match_feedback',
        'responsibilities_qualifications_score',
        'responsibilities_qualifications_feedback',
        'industry_keywords_synonyms_score',
        'industry_keywords_synonyms_feedback',
    ];

    protected $casts = [
        'skills_match_score' => 'integer',
        'job_title_match_score' => 'integer',
        'responsibilities_qualifications_score' => 'integer',
        'industry_keywords_synonyms_score' => 'integer',
    ];

    public function profileEvaluation(): BelongsTo
    {
        return $this->belongsTo(ProfileEvaluation::class);
    }
}