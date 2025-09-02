<?php

use Inertia\Testing\AssertableInertia as Assert;

it('includes the answer in the evaluation show props', function () {
    $evaluation = \App\Models\InterviewAnswerEvaluation::factory()->create([
        'answer' => "This is my structured interview answer.\nIt has multiple lines.",
    ]);

    $user = \App\Models\User::find($evaluation->user_id);
    $this->actingAs($user);

    $response = $this->withHeader('X-Inertia', 'true')
        ->get(route('interview-answer-evaluations.show', $evaluation));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('interview/InterviewAnswerEvaluation')
        ->where('evaluation.id', $evaluation->id)
        ->where('evaluation.answer', $evaluation->answer)
    );
});
