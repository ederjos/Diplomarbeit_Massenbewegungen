<?php

use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('user can toggle favorite on a project they belong to', function () {
    /** @var \Tests\TestCase $this */
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
    $response->assertStatus(200)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Home')
                ->has('projects', 1)
                ->where('projects.0.isFavorite', true)
        );
});

test('user cannot toggle favorite on a project they are not a member of', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->createOne();
    /** @var User $user */
    $user = User::factory()->createOne();

    $response = $this->actingAs($user)->post(route('project.toggleFavorite', $project));

    // Forbidden
    $response->assertStatus(403);
});

test('unauthenticated user cannot toggle favorite', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->createOne();

    $response = $this->post(route('project.toggleFavorite', $project));

    // Forbidden
    $response->assertRedirect('/login');
});
