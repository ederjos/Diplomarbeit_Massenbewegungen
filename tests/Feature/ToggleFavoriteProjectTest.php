<?php

use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

describe('Toggle favorite project', function () {
    it('allows a member to toggle a project as favorite', function () {
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

    it('forbids non-members from toggling project favorites', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();
        /** @var User $user */
        $user = User::factory()->createOne();

        $response = $this->actingAs($user)->post(route('project.toggleFavorite', $project));

        // Forbidden
        $response->assertForbidden();
    });

    it('redirects unauthenticated users when toggling favorites', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();

        $response = $this->post(route('project.toggleFavorite', $project));

        // Forbidden
        $response->assertRedirect('/login');
    });
});
