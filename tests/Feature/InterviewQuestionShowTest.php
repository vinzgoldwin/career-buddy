<?php

use App\Models\InterviewQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('shows question details with explanation when available', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $q = InterviewQuestion::factory()->create([
        'title' => 'Tell me about your biggest failure.',
        'category' => 'Behavioral',
        'difficulty' => 'Hard',
        'explanation' => 'Sample explanation for test.',
    ]);

    actingAs($user);

    $response = get(route('interview-question-bank.show', $q));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('interview/InterviewQuestion')
        ->where('question.title', 'Tell me about your biggest failure.')
        ->where('question.explanation', 'Sample explanation for test.')
    );
});
