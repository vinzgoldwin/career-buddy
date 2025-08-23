<?php

use App\Models\JobDescription;
use App\Models\ProfileEvaluation;
use App\Models\User;

it('shows evaluation history for the authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $job = JobDescription::create([
        'user_id' => $user->id,
        'title' => 'Software Engineer',
        'seniority' => 'Mid',
        'company_name' => 'Acme Inc',
        'work_mode' => 'Remote',
        'location' => 'Anywhere',
        'employment_type' => 'Full-time',
        'summary' => 'Build great products',
        'responsibilities' => json_encode(['Develop software']),
        'requirements' => json_encode(['3+ years']),
        'skills' => json_encode([['name' => 'PHP', 'proficiency_level' => 4]]),
        'years_experience_min' => 3,
        'years_experience_max' => 7,
        'raw_input' => 'Raw JD',
    ]);

    ProfileEvaluation::create([
        'user_id' => $user->id,
        'job_description_id' => $job->id,
        'total_score' => 90,
        'strengths' => 'Strong skills',
        'areas_for_improvement' => 'None',
    ]);

    $response = $this->get(route('ai-evaluation.index'));
    $response->assertStatus(200);
    $response->assertSee('Software Engineer');
    $response->assertSee('Build great products');
});
