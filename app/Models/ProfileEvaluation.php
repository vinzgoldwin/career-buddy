<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_description_id',
        'total_score',
        'impact',
        'skills_and_traits',
        'alignment_with_job',
        'overall_recommendation',
        'improvements',
        'specific_changes',
        'raw_output',
        'errors',
        'usage',
        'llm_model',
    ];

    protected function casts(): array
    {
        return [
            'impact' => 'array',
            'skills_and_traits' => 'array',
            'alignment_with_job' => 'array',
            'specific_changes' => 'array',
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
}
