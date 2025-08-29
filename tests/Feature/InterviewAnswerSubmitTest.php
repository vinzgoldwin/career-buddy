<?php

namespace Tests\Feature;

use App\Models\InterviewQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Laravel\Facades\OpenAI;
use Tests\TestCase;

class InterviewAnswerSubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_answer_endpoint_validates_and_saves(): void
    {
        $user = User::factory()->create();
        $q = InterviewQuestion::factory()->create();

        $content = <<<EOT
<evaluation>
<overall_performance>Good.\nScore: 6</overall_performance>
<structural_integrity>Okay.\nScore: 5</structural_integrity>
<content_accuracy>Solid.\nScore: 7</content_accuracy>
<fluency_of_expression>Clear.\nScore: 6</fluency_of_expression>
<strengths>1. Clear terms\n2. On topic</strengths>
<priority_areas_for_improvement>1. More detail</priority_areas_for_improvement>
<comparative_analysis>✅ Above average clarity\n❌ Less depth than expert</comparative_analysis>
<encouraging_advice>1. Practice structure</encouraging_advice>
</evaluation>
EOT;

        config()->set('openai.api_key', 'test-key');
        OpenAI::swap(new class($content)
        {
            public function __construct(private string $content) {}
            public function chat()
            {
                $content = $this->content;
                return new class($content)
                {
                    public function __construct(private string $content) {}
                    public function create(array $params)
                    {
                        $content = $this->content;
                        return new class($content)
                        {
                            public function __construct(private string $content) {}
                            public function toArray(): array
                            {
                                return [
                                    'choices' => [
                                        ['message' => ['content' => $this->content]],
                                    ],
                                    'model' => 'test-model',
                                    'usage' => [],
                                ];
                            }
                        };
                    }
                };
            }
        });

        $this->actingAs($user);

        $res = $this->postJson(route('interview-question-bank.answer.store', $q), [
            'answer' => 'This is my answer',
        ]);

        $res->assertSuccessful();
        $res->assertJsonStructure([
            'id',
            'data' => [
                'overall_performance' => ['score', 'justification'],
                'structural_integrity' => ['score', 'justification'],
                'content_accuracy' => ['score', 'justification'],
                'fluency_of_expression' => ['score', 'justification'],
                'strengths',
                'priority_areas_for_improvement',
                'comparative_analysis',
                'encouraging_advice',
            ],
        ]);

        $this->assertDatabaseHas('interview_answer_evaluations', [
            'interview_question_id' => $q->id,
            'user_id' => $user->id,
            'overall_performance_score' => 6,
            'structural_integrity_score' => 5,
            'content_accuracy_score' => 7,
            'fluency_of_expression_score' => 6,
        ]);
    }
}

