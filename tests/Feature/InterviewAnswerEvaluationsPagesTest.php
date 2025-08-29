<?php

use App\Models\InterviewAnswerEvaluation;
use App\Models\InterviewQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('lists interview answer evaluations', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $q = InterviewQuestion::factory()->create();
    InterviewAnswerEvaluation::create([
        'interview_question_id' => $q->id,
        'user_id' => $user->id,
        'answer' => 'Test',
        'overall_performance_score' => 7,
    ]);

    actingAs($user);
    $response = get(route('interview-answer-evaluations.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('ai/InterviewAnswerEvaluations')
        ->has('evaluations.data', 1)
    );
});

it('shows a single interview answer evaluation', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $q = InterviewQuestion::factory()->create(['title' => 'Tell me about yourself']);
    $e = InterviewAnswerEvaluation::create([
        'interview_question_id' => $q->id,
        'user_id' => $user->id,
        'answer' => 'Answer',
        'overall_performance_score' => 6,
        'structural_integrity_score' => 5,
        'content_accuracy_score' => 7,
        'fluency_of_expression_score' => 6,
        'raw_output' => '<evaluation><overall_performance>Good.\nScore: 6</overall_performance></evaluation>',
    ]);

    actingAs($user);
    $response = get(route('interview-answer-evaluations.show', $e));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('ai/InterviewAnswerEvaluation')
        ->where('evaluation.id', $e->id)
        ->where('evaluation.question.title', 'Tell me about yourself')
    );
});

