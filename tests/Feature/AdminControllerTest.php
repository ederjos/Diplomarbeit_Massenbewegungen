<?php

use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

test('admin can access admin dashboard', function () {
    /** @var TestCase $this */
    /** @var Role $role */
    $role = Role::factory()->createOne(['is_admin' => true]);
    /** @var User $admin */
    $admin = User::factory()->createOne(['role_id' => $role->id]);

    $response = $this->actingAs($admin)->get(route('admin'));

    $response->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Admin')
        );
});

test('non-admin cannot access admin dashboard', function () {
    /** @var TestCase $this */
    /** @var Role $role */
    $role = Role::factory()->createOne(['is_admin' => false]);
    /** @var User $user */
    $user = User::factory()->createOne(['role_id' => $role->id]);

    $response = $this->actingAs($user)->get(route('admin'));

    // Forbidden
    $response->assertForbidden();
});

test('unauthenticated user cannot access admin dashboard', function () {
    /** @var TestCase $this */
    $response = $this->get(route('admin'));

    // Redirect to login
    $response->assertRedirect(route('login'));
});

test('admin can approve registration request', function () {
    /** @var TestCase $this */
    /** @var Role $role */
    $role = Role::factory()->createOne(['is_admin' => true]);
    /** @var User $admin */
    $admin = User::factory()->createOne(['role_id' => $role->id]);

    // The role to assign to the new user upon approval
    $targetRole = Role::factory()->createOne();

    // Create a registration request
    $registrationRequest = RegistrationRequest::factory()->createOne();

    $response = $this->actingAs($admin)->post(route('admin.registration-requests.approve', $registrationRequest), ['role_id' => $targetRole->id]);

    // Redirect back to admin dashboard
    $response->assertRedirect(route('admin'));

    // Check if the user was created
    $this->assertDatabaseMissing('registration_requests', ['id' => $registrationRequest->id]);
    $this->assertDatabaseHas('users', [
        'email' => $registrationRequest->email,
        'name' => $registrationRequest->name,
        'role_id' => $targetRole->id,
    ]);
});

test('admin can reject registration request', function () {
    /** @var TestCase $this */
    /** @var Role $role */
    $role = Role::factory()->createOne(['is_admin' => true]);
    /** @var User $admin */
    $admin = User::factory()->createOne(['role_id' => $role->id]);

    // Create a registration request
    $registrationRequest = RegistrationRequest::factory()->createOne();

    $response = $this->actingAs($admin)->delete(route('admin.registration-requests.reject', $registrationRequest));

    // Redirect back to admin dashboard
    $response->assertRedirect(route('admin'));

    // Check if the registration request was deleted and no user was created
    $this->assertDatabaseMissing('registration_requests', ['id' => $registrationRequest->id]);
    $this->assertDatabaseMissing('users', ['email' => $registrationRequest->email]);
});
