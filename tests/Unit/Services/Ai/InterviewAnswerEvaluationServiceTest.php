<?php

namespace Tests\Unit\Services\Ai;

use App\Models\InterviewQuestion;
use App\Services\Ai\InterviewAnswerEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Laravel\Facades\OpenAI;
use Tests\TestCase;

class InterviewAnswerEvaluationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluate_and_store_parses_sections_and_scores()
    {
        $question = InterviewQuestion::factory()->create([
            'title' => 'What is polymorphism in OOP?',
            'category' => 'OOP',
            'explanation' => 'Polymorphism allows methods to do different things based on the object it is acting upon.',
        ])->toArray();

        $content = <<<EOT
<evaluation>
<overall_performance>
Solid explanation with examples.
Score: 8
</overall_performance>

<structural_integrity>
Clear intro, body, and brief conclusion.
Score: 7
</structural_integrity>

<content_accuracy>
Accurate definition and correct examples.
Score: 9
</content_accuracy>

<fluency_of_expression>
Concise and readable, minor hesitations.
Score: 7
</fluency_of_expression>

<strengths>
1. Accurate definition
2. Relevant examples
</strengths>

<priority_areas_for_improvement>
1. Add contrasts to similar concepts
2. Include performance considerations
</priority_areas_for_improvement>

<comparative_analysis>
✅ Definition clarity aligns with expert baseline
✅ Examples exceed average breadth
❌ Lacks depth vs expert on trade-offs
</comparative_analysis>

<encouraging_advice>
1. Keep practicing concise examples
2. Add one advanced nuance next time
</encouraging_advice>
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

        $service = new InterviewAnswerEvaluationService();
        $result = $service->evaluateAndStore($question, 'User answer here.');

        $model = $result['model'];
        $this->assertNotNull($model->id);
        $this->assertEquals(8, $model->overall_performance_score);
        $this->assertEquals(7, $model->structural_integrity_score);
        $this->assertEquals(9, $model->content_accuracy_score);
        $this->assertEquals(7, $model->fluency_of_expression_score);
        $this->assertIsArray($model->strengths);
        $this->assertContains('Accurate definition', $model->strengths);
        $this->assertIsArray($model->priority_areas_for_improvement);
        $this->assertIsArray($model->comparative_analysis);
        $this->assertIsArray($model->encouraging_advice);
    }
}

