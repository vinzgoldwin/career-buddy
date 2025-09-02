<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;

it('requires auth for /api/profile', function () {
    $response = $this->get('/api/profile');

    // Web auth middleware redirects to login when unauthenticated
    $response->assertRedirect();
});

it('returns profile JSON for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->getJson('/api/profile');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'profile' => [
                'id', 'name', 'email', 'phone', 'location', 'website', 'summary',
                'educations', 'experiences', 'licenses_and_certifications', 'projects', 'skills',
            ],
        ])
        ->assertJsonPath('profile.id', $user->id);
});

it('returns profile JSON for valid signed request', function () {
    $user = User::factory()->create();

    $url = URL::temporarySignedRoute('api.profile.signed', now()->addMinutes(10), [
        'user' => $user->id,
    ]);

    $response = $this->getJson($url);

    $response->assertSuccessful()
        ->assertJsonPath('profile.id', $user->id);
});

it('rejects unsigned or invalid signed requests', function () {
    $user = User::factory()->create();

    $url = route('api.profile.signed', ['user' => $user->id]);

    $this->getJson($url)->assertForbidden();
});

it('generates a signed URL for current user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $res = $this->getJson('/api/profile/signed/generate');
    $res->assertSuccessful()->assertJsonStructure(['url', 'expires_at']);

    $url = $res->json('url');
    expect($url)->toContain('/api/profile/signed');
});
