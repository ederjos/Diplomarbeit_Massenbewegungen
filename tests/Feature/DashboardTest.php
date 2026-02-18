<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get(route('home'));
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    /** @var \Tests\TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));
    $response->assertStatus(200);
});
