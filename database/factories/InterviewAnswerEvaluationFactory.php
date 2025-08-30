<?php

namespace Database\Factories;

use App\Models\InterviewAnswerEvaluation;
use App\Models\InterviewQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\InterviewAnswerEvaluation>
 */
class InterviewAnswerEvaluationFactory extends Factory
{
    protected $model = InterviewAnswerEvaluation::class;

    public function definition(): array
    {
        $rawOutput = <<<'XML'
<overall_performance>
Solid overall answer structure with relevant examples.
Score: 8
</overall_performance>
<structural_integrity>
Clear beginning, middle, and end, with STAR formatting.
Score: 7
</structural_integrity>
<content_accuracy>
Accurate and aligned to the question topic.
Score: 9
</content_accuracy>
<fluency_of_expression>
Natural tone and confident delivery.
Score: 8
</fluency_of_expression>
XML;

        return [
            'interview_question_id' => InterviewQuestion::factory(),
            'user_id' => User::factory(),
            'answer' => $this->faker->paragraph(),
            'overall_performance_score' => 8,
            'structural_integrity_score' => 7,
            'content_accuracy_score' => 9,
            'fluency_of_expression_score' => 8,
            'strengths' => [
                'Clear structure',
                'Relevant examples',
            ],
            'priority_areas_for_improvement' => [
                'Add more metrics',
                'Tighten conclusion',
            ],
            'comparative_analysis' => [
                '✅ Better than average clarity',
                '❌ Weaker than peers on specifics',
            ],
            'encouraging_advice' => [
                'You are on the right path — keep practicing!',
                'Consider timing and pauses for emphasis.',
            ],
            'raw_output' => $rawOutput,
            'errors' => null,
            'usage' => null,
            'llm_model' => 'gpt-4o-mini',
        ];
    }
}
