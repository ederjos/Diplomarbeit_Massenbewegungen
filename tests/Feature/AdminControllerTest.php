<?php

use App\Models\Measurement;
use App\Models\Point;
use App\Models\Project;
use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

describe('Admin controller', function () {
    it('allows an admin to access the admin dashboard', function () {
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

    it('blocks non-admin users from the admin dashboard', function () {
        /** @var TestCase $this */
        /** @var Role $role */
        $role = Role::factory()->createOne(['is_admin' => false]);
        /** @var User $user */
        $user = User::factory()->createOne(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('admin'));

        // Forbidden
        $response->assertForbidden();
    });

    it('redirects unauthenticated users from the admin dashboard', function () {
        /** @var TestCase $this */
        $response = $this->get(route('admin'));

        // Redirect to login
        $response->assertRedirect(route('login'));
    });

    it('allows an admin to approve a registration request', function () {
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

    it('allows an admin to reject a registration request', function () {
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

    it('allows an admin to open the measurement import page', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['is_admin' => true]);
        $admin = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne(['name' => 'Bergprojekt']);
        Measurement::factory()->createOne(['project_id' => $project->id, 'name' => 'FM27']);

        $response = $this->actingAs($admin)->get(route('project.measurements.import.create', $project));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/ImportMeasurements')
                ->where('project.id', $project->id)
                ->where('project.name', 'Bergprojekt')
                ->where('project.previousMeasurementName', 'FM27'));
    });

    it('imports a measurement and scopes point matching to the selected project', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['is_admin' => true]);
        $admin = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne();
        $otherProject = Project::factory()->createOne();
        $existingPoint = Point::factory()->createOne([
            'project_id' => $project->id,
            'name' => 'P-1',
            'projection_id' => null,
        ]);
        Point::factory()->createOne(['project_id' => $otherProject->id, 'name' => 'P-2']);

        $response = $this->actingAs($admin)->post(route('project.measurements.import.store', $project), [
            'name' => '2026-09-23',
            'measurement_datetime' => '2026-09-23T12:30',
            'file' => UploadedFile::fake()->createWithContent(
                'measurement.csv',
                "P-1;1.25;2.5;3.75\nP-2;4.0;5.0;6.0\n",
            ),
        ]);

        $measurement = Measurement::query()->where('project_id', $project->id)->firstOrFail();
        $response->assertRedirect(route('project', $project));
        $this->assertDatabaseHas('measurements', [
            'id' => $measurement->id,
            'project_id' => $project->id,
            'name' => '2026-09-23',
        ]);
        $this->assertDatabaseCount('points', 3);
        $this->assertDatabaseCount('measurement_values', 2);
        $this->assertDatabaseHas('measurement_values', [
            'measurement_id' => $measurement->id,
            'point_id' => $existingPoint->id,
            'x' => 1.25,
        ]);
        $importedValue = $measurement->measurementValues()->where('point_id', $existingPoint->id)->firstOrFail();
        expect($importedValue->geom->getSrid())
            ->toBe(config('spatial.srids.default'))
            ->and($importedValue->geom->getX())->toBe(1.25)
            ->and($importedValue->geom->getY())->toBe(2.5)
            ->and($importedValue->geom->getZ())->toBe(3.75);
        $this->assertDatabaseHas('points', [
            'project_id' => $project->id,
            'name' => 'P-2',
            'is_visible' => true,
            'projection_id' => null,
        ]);
    });

    it('rejects a duplicate measurement name without writing any rows', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['is_admin' => true]);
        $admin = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne();
        Measurement::factory()->createOne(['project_id' => $project->id, 'name' => 'existing']);

        $response = $this->actingAs($admin)->post(route('project.measurements.import.store', $project), [
            'name' => 'existing',
            'measurement_datetime' => '2026-09-23T12:30',
            'file' => UploadedFile::fake()->createWithContent('measurement.csv', "P-1;1;2;3\n"),
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('measurement_values', 0);
    });

    it('blocks non-admins from importing measurements', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['is_admin' => false]);
        $user = User::factory()->createOne(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('project.measurements.import.create', Project::factory()->createOne()));

        $response->assertForbidden();
    });

    it('rejects CSV files above the measurement point limit', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['is_admin' => true]);
        $admin = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne();
        $csv = collect(range(1, 10001))
            ->map(fn (int $number): string => "P-{$number};1;2;3")
            ->implode("\n");

        $response = $this->actingAs($admin)->post(route('project.measurements.import.store', $project), [
            'name' => 'too-large',
            'measurement_datetime' => '2026-09-23T12:30',
            'file' => UploadedFile::fake()->createWithContent('measurement.csv', $csv),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('measurements', ['name' => 'too-large']);
    });

    it('rejects malformed CSV rows without creating a measurement', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['is_admin' => true]);
        $admin = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne();

        $response = $this->actingAs($admin)->post(route('project.measurements.import.store', $project), [
            'name' => 'malformed',
            'measurement_datetime' => '2026-09-23T12:30',
            'file' => UploadedFile::fake()->createWithContent('measurement.csv', "P-1;1;2\n"),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('measurements', ['name' => 'malformed']);
    });
});
