<?php

use App\Models\Measurement;
use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

describe('Projects overview', function () {
    /**
     * pest()->use(RefreshDatabase::class);
     * https://laravel.com/docs/12.x/database-testing
     * RefreshDatabase does not migrate your database if your schema is up to date.
     * Instead, it will only execute the test within a database transaction.
     * Therefore, any records added to the database by test cases
     * that do not use this trait may still exist in the database.
     * -> Defined globally in Pest.php
     */
    it('calculates the next measurement correctly on the home page', function () {
        /** @var TestCase $this */
        // For intelephense autocomplete

        // Create a fake project
        $project = Project::factory()->createOne([
            // PostgreSQL Interval
            'period' => '1 year',
            'is_active' => true,
        ]);

        // Create a measurement
        Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2000-01-01 10:00:00',
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);
        // the names from web.php
        $response = $this->actingAs($user)->get(route('home'));

        // Check the next measurement is calculated correctly (1 year later)
        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has('projects', 1)
                    ->where('projects.0.isActive', true)
                    // 2000 + 1 year
                    ->where('projects.0.nextMeasurement', '2001-01-01 10:00:00')
            );
    });

    it('does not show the next measurement for inactive projects', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne([
            'period' => '1 year',
            'is_active' => false,
        ]);

        Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2000-01-01 10:00:00',
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);
        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk()
            ->assertInertia(
                // Same syntax as in docs
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has('projects', 1)
                    ->where('projects.0.isActive', false)
                    ->where('projects.0.nextMeasurement', null)
            );
    });

    it('shows no measurement dates for projects without measurements', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne([
            'is_active' => false,
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);
        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->where('projects.0.lastMeasurement', null)
                    ->where('projects.0.nextMeasurement', null)
            );
    });
});
