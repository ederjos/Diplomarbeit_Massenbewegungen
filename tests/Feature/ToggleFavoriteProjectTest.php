<?php

use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

test('user can toggle favorite on a project they belong to', function () {
    /** @var TestCase $this */
    // Create a fake objects
    /** @var Project $project */
    $project = Project::factory()->createOne();
    /** @var User $user */
    $user = User::factory()->createOne();
    $project->users()->attach($user->id, ['is_contact_person' => false, 'is_favorite' => false]);

    // Now mark as favorite
    $this->actingAs($user)->post(route('project.toggleFavorite', $project))
        // Forward to home
        ->assertRedirect('/');

    $response = $this->actingAs($user)->get(route('home'));

    // Check the next measurement is calculated correctly (1 year later)
    $response->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Home')
                ->has('projects', 1)
                ->where('projects.0.isFavorite', true)
        );
});

test('user cannot toggle favorite on a project they are not a member of', function () {
    /** @var TestCase $this */
    $project = Project::factory()->createOne();
    /** @var User $user */
    $user = User::factory()->createOne();

    $response = $this->actingAs($user)->post(route('project.toggleFavorite', $project));

    // Forbidden
    $response->assertForbidden();
});

test('unauthenticated user cannot toggle favorite', function () {
    /** @var TestCase $this */
    $project = Project::factory()->createOne();

    $response = $this->post(route('project.toggleFavorite', $project));

    // Forbidden
    $response->assertRedirect('/login');
});
