<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'seniority',
        'company_name',
        'work_mode',
        'location',
        'employment_type',
        'summary',
        'responsibilities',
        'requirements',
        'skills',
        'years_experience_min',
        'years_experience_max',
        'raw_input',
        'llm_output_raw',
        'errors',
        'usage',
        'llm_model',
    ];

    protected function casts(): array
    {
        return [
            'responsibilities' => 'array',
            'requirements' => 'array',
            'skills' => 'array',
            'years_experience_min' => 'integer',
            'years_experience_max' => 'integer',
            'errors' => 'array',
            'usage' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
