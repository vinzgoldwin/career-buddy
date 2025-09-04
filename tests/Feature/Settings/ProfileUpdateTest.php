<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;
use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

it('updates autofill-related profile fields', function () {
    $user = User::factory()->create();

    actingAs($user);
    withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

    $payload = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '+1 555 0100',
        'location' => 'Seattle, WA',
        'website' => 'https://www.linkedin.com/in/jane',
        'summary' => 'Seasoned engineer with focus on DX.',
    ];

    patch(route('profile.update'), $payload)->assertRedirect();

    $user->refresh();
    expect($user->name)->toBe('Jane Doe')
        ->and($user->email)->toBe('jane@example.com')
        ->and($user->phone)->toBe('+1 555 0100')
        ->and($user->location)->toBe('Seattle, WA')
        ->and($user->website)->toBe('https://www.linkedin.com/in/jane')
        ->and($user->summary)->toBe('Seasoned engineer with focus on DX.');
});
