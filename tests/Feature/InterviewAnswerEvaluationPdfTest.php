<?php

it('downloads PDF for an interview evaluation', function () {
    $evaluation = \App\Models\InterviewAnswerEvaluation::factory()->create();

    $user = $evaluation->user_id
        ? \App\Models\User::find($evaluation->user_id)
        : \App\Models\User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('interview-answer-evaluations.download', $evaluation));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');

    $content = $response->streamedContent();
    expect($content)->toStartWith('%PDF');
});
