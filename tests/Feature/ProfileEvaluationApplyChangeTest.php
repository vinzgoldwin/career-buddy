<?php

use App\Models\Experience;
use App\Models\ProfileEvaluation;
use App\Models\ProfileEvaluationSpecificChange;
use App\Models\User;

it('applies summary change', function () {
    $user = User::factory()->create(['summary' => 'Old']);
    $this->actingAs($user);

    $evaluation = ProfileEvaluation::create([
        'user_id' => $user->id,
        'total_score' => 50,
    ]);

    $change = ProfileEvaluationSpecificChange::create([
        'profile_evaluation_id' => $evaluation->id,
        'field' => 'summary',
        'entity_id' => 0,
        'specific_field' => 'summary',
        'old_value' => 'Old',
        'new_value' => 'New Summary',
    ]);

    $response = $this->post(route('ai-evaluation.apply-change', [$evaluation, $change]));
    $response->assertStatus(302);

    expect($user->fresh()->summary)->toBe('New Summary');
    expect($change->fresh()->applied_at)->not->toBeNull();
});

it('applies experience description change', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $exp = Experience::create([
        'user_id' => $user->id,
        'title' => 'Dev',
        'company' => 'Acme',
        'location' => 'Remote',
        'description' => 'Old desc',
        'start_date' => now(),
    ]);

    $evaluation = ProfileEvaluation::create([
        'user_id' => $user->id,
        'total_score' => 50,
    ]);

    $change = ProfileEvaluationSpecificChange::create([
        'profile_evaluation_id' => $evaluation->id,
        'field' => 'experiences',
        'entity_id' => $exp->id,
        'specific_field' => 'description',
        'old_value' => 'Old desc',
        'new_value' => 'New desc',
    ]);

    $this->post(route('ai-evaluation.apply-change', [$evaluation, $change]))->assertStatus(302);

    expect($exp->fresh()->description)->toBe('New desc');
});
