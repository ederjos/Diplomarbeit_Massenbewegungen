<?php

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
            );
    });
});
