<?php

use App\Models\User;
use Tests\TestCase;

describe('Dashboard', function () {
    it('redirects guests to the login page', function () {
        /** @var TestCase $this */
        $response = $this->get(route('home'));
        $response->assertRedirect('/login');
    });

    it('allows authenticated users to visit the dashboard', function () {
        /** @var TestCase $this */

        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('home'));
        $response->assertOk();
    });
});
