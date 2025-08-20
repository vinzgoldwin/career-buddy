<?php

use App\Models\User;

it('accepts a raw job description and returns parsed JSON and profile', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $raw = <<<'TXT'
About the job
About Accenture

We are seeking a Node.js Developer to join our team with at least 3 years of experience and 2 years of experience in the banking sector.

Key Responsibilities:
- Build APIs
- Work in Agile

Required Qualifications:
- 3+ years of experience in backend development using Node.js.
- Experience with RESTful APIs and GraphQL
- Experience in AWS
TXT;

    $response = $this->postJson(route('ai-resume-builder.parse-job'), [
        'raw' => $raw,
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'job' => [
            'company' => ['name', 'website', 'about'],
            'role' => ['title', 'department', 'seniority'],
            'summary',
            'responsibilities',
            'qualifications' => ['required', 'preferred'],
            'experience' => ['total_years_min', 'domain_experience'],
            'technologies',
            'location',
            'employment_type',
            'compensation',
            'notes',
            'raw',
        ],
        'profile' => [
            'id', 'name', 'email', 'phone', 'location', 'website', 'summary',
            'educations', 'experiences', 'licenses_and_certifications', 'projects', 'skills',
        ],
    ]);
});
