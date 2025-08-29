<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewAnswerEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_question_id',
        'user_id',
        'answer',
        'overall_performance_score',
        'structural_integrity_score',
        'content_accuracy_score',
        'fluency_of_expression_score',
        'strengths',
        'priority_areas_for_improvement',
        'comparative_analysis',
        'encouraging_advice',
        'raw_output',
        'errors',
        'usage',
        'llm_model',
    ];

    public function casts(): array
    {
        return [
            'interview_question_id' => 'integer',
            'user_id' => 'integer',
            'overall_performance_score' => 'integer',
            'structural_integrity_score' => 'integer',
            'content_accuracy_score' => 'integer',
            'fluency_of_expression_score' => 'integer',
            'strengths' => 'array',
            'priority_areas_for_improvement' => 'array',
            'comparative_analysis' => 'array',
            'encouraging_advice' => 'array',
            'usage' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function question()
    {
        return $this->belongsTo(InterviewQuestion::class, 'interview_question_id');
    }
}

