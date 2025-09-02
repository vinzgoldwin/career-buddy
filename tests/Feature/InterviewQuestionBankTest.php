<?php

use App\Models\InterviewQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('shows paginated interview questions with filters', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    // Seed a batch of questions
    InterviewQuestion::factory()->count(25)->create();

    // Add a specific one to filter for
    InterviewQuestion::factory()->create([
        'title' => 'Why do you want to work at TestCo?',
        'category' => 'Company-Specific',
        'difficulty' => 'Medium',
    ]);

    actingAs($user);

    // First page should show 10
    $response = get(route('interview-question-bank'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('interview/InterviewQuestionBank')
        ->has('questions.data', 10)
        ->has('questions.links')
        ->has('categories')
        ->has('difficulties')
    );

    // Filter by search, category and difficulty
    $response = get(route('interview-question-bank', [
        'search' => 'TestCo',
        'category' => 'Company-Specific',
        'difficulty' => 'Medium',
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('filters.search', 'TestCo')
        ->where('filters.category', 'Company-Specific')
        ->where('filters.difficulty', 'Medium')
        ->has('questions.data', 1)
    );
});
