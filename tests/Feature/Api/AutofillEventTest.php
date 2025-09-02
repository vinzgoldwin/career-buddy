<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;

it('stores an autofill event via signed params', function () {
    $user = User::factory()->create();

    $expiresAt = now()->addMinutes(10);
    $url = URL::temporarySignedRoute('api.profile.signed', $expiresAt, ['user' => $user->id]);
    parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $q);

    $payload = [
        'user' => $user->id,
        'expires' => (int) $q['expires'],
        'signature' => (string) $q['signature'],
        'resume_variant' => 'Backend v2',
        'job_title' => 'Software Engineer',
        'company' => 'Acme Inc',
        'source_host' => 'boards.greenhouse.io',
        'page_url' => 'https://boards.greenhouse.io/acme/jobs/123',
        'fields' => ['name', 'email', 'phone'],
        'field_details' => [
            ['type' => 'name', 'name' => 'full_name', 'id' => 'applicant_name'],
            ['type' => 'email', 'name' => 'email', 'id' => 'applicant_email'],
        ],
        'filled_count' => 3,
    ];

    $res = $this->postJson('/api/autofill-events/signed', $payload);
    $res->assertSuccessful()->assertJsonPath('ok', true);
});
