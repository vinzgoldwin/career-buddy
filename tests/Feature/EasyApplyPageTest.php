<?php

use App\Models\User;

test('guests are redirected to the login page for easy-apply', function () {
    $response = $this->get('/easy-apply');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the easy-apply page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/easy-apply');
    $response->assertStatus(200);
});
