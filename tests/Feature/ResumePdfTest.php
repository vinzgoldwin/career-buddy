<?php

use App\Models\Education;
use App\Models\Experience;
use App\Models\LicenseAndCertification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

it('downloads an ATS-friendly resume PDF for the current user', function () {
    $user = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'email_verified_at' => now(),
        'summary' => 'Seasoned professional with a track record of impact.',
        'location' => 'Remote',
        'website' => 'https://example.com',
    ]);

    Experience::factory()->create([
        'user_id' => $user->id,
        'title' => 'Senior Engineer',
        'company' => 'Acme Inc',
        'start_date' => '2023-01-01',
        'end_date' => null,
        'currently_working' => true,
        'description' => "Led a team of 5 engineers.\nImproved system performance by 30%.",
    ]);

    Education::factory()->create([
        'user_id' => $user->id,
        'school' => 'State University',
        'degree' => 'B.Sc.',
        'field_of_study' => 'Computer Science',
        'start_date' => '2015-09-01',
        'end_date' => '2019-06-01',
        'currently_studying' => false,
    ]);

    Project::factory()->create([
        'user_id' => $user->id,
        'name' => 'Internal Tooling',
        'description' => 'Built an internal developer portal to streamline workflows.',
        'start_date' => '2022-02-01',
        'end_date' => '2022-10-01',
        'url' => 'https://example.com/tool',
    ]);

    LicenseAndCertification::factory()->create([
        'user_id' => $user->id,
        'name' => 'AWS Certified Solutions Architect',
        'issuing_organization' => 'Amazon',
        'issue_date' => '2021-05-01',
        'expiration_date' => '2024-05-01',
        'credential_id' => 'ABC-123',
    ]);

    actingAs($user);

    $response = get(route('ai-resume-builder.download'));

    $response->assertOk();
    expect($response->headers->get('content-type'))
        ->toContain('application/pdf');
    expect(Str::lower((string) $response->headers->get('content-disposition')))
        ->toContain('attachment;');

    // Dompdf output begins with %PDF...
    expect(substr($response->streamedContent(), 0, 10))
        ->toContain('PDF');
});
