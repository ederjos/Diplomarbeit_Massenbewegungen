<?php

use Tests\TestCase;

test('guest can submit a registration request', function () {
    /** @var TestCase $this */
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'SecurePassword1234!',
        'password_confirmation' => 'SecurePassword1234!',
        'note' => 'Please approve me',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseHas('registration_requests', ['email' => 'test@example.com']);
});

test('registration request requires name, valid email, strong password and not too long note', function () {
    /** @var TestCase $this */
    $this->post(route('register.store'), [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'does-not-match',
        // too long
        'note' => str_repeat('a', 1001),
        // should result in validation failure for all fields (errors stored in sessions)
    ])->assertSessionHasErrors(['name', 'email', 'password', 'note']);
});
