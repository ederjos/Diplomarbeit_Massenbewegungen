<?php

use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\Point;
use App\Models\Project;
use App\Models\Projection;
use App\Models\User;
use Tests\TestCase;

describe('Displacements for pair API', function () {
    it('rejects unauthenticated access to the displacements API', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();

        $response = $this->getJson(route('project.displacements', $project));

        $response->assertUnauthorized();
    });

    it('forbids non-members from the displacements API', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();

        $referenceMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2025-01-01 00:00:00',
        ]);

        $comparisonMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2026-01-01 00:00:00',
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();
        // User is NOT attached to the project

        /**
         * Claude Sonnet 4.6, 2026-03-03
         * "And how can I test the displacements API using its route? [...]"
         */
        $response = $this->actingAs($user)->getJson(
            route('project.displacements', $project).'?reference='.$referenceMeasurement->id.'&comparison='.$comparisonMeasurement->id
        );

        $response->assertForbidden();
    });

    it('returns 404 when reference measurement does not belong to project', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();
        $otherProject = Project::factory()->createOne();

        $validMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2025-01-01 00:00:00',
        ]);

        $foreignMeasurement = Measurement::factory()->createOne([
            'project_id' => $otherProject->id,
            'measurement_datetime' => '2026-01-01 00:00:00',
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->getJson(
            route('project.displacements', $project).'?reference='.$foreignMeasurement->id.'&comparison='.$validMeasurement->id
        );

        $response->assertNotFound();
    });

    it('returns 404 when comparison measurement does not belong to project', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();
        $otherProject = Project::factory()->createOne();

        $validMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2025-01-01 00:00:00',
        ]);

        $foreignMeasurement = Measurement::factory()->createOne([
            'project_id' => $otherProject->id,
            'measurement_datetime' => '2026-01-01 00:00:00',
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->getJson(
            route('project.displacements', $project).'?reference='.$validMeasurement->id.'&comparison='.$foreignMeasurement->id
        );

        $response->assertNotFound();
    });

    it('returns correct displacement values for a pair', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();

        $referenceMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2025-01-01 00:00:00',
        ]);

        $comparisonMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2026-01-01 00:00:00',
        ]);

        // ax=1, ay=0 means projection along the X axis
        $projection = Projection::factory()->createOne([
            'ax' => 1.0,
            'ay' => 0.0,
        ]);

        $point = Point::factory()->createOne([
            'project_id' => $project->id,
            'projection_id' => $projection->id,
            'is_visible' => true,
        ]);

        $referenceCoords = [100.0, 200.0, 300.0];
        // dX=3, dY=4, dZ=12
        $comparisonCoords = [103.0, 204.0, 312.0];

        MeasurementValue::factory()->createOne([
            'point_id' => $point->id,
            'measurement_id' => $referenceMeasurement->id,
            'x' => $referenceCoords[0],
            'y' => $referenceCoords[1],
            'z' => $referenceCoords[2],
            'addition_id' => null,
        ]);

        MeasurementValue::factory()->createOne([
            'point_id' => $point->id,
            'measurement_id' => $comparisonMeasurement->id,
            'x' => $comparisonCoords[0],
            'y' => $comparisonCoords[1],
            'z' => $comparisonCoords[2],
            'addition_id' => null,
        ]);

        // Expected values in centimeters
        $expectedDx = $comparisonCoords[0] - $referenceCoords[0]; // 3
        $expectedDy = $comparisonCoords[1] - $referenceCoords[1]; // 4
        $expectedDz = $comparisonCoords[2] - $referenceCoords[2]; // 12

        $expected2d = sqrt($expectedDx ** 2 + $expectedDy ** 2) * 100; // 500
        $expected3d = sqrt($expectedDx ** 2 + $expectedDy ** 2 + $expectedDz ** 2) * 100; // 1300
        $expectedProjection = abs($expectedDx * 1.0 + $expectedDy * 0.0) * 100; // 300
        $expectedDeltaHeight = $expectedDz * 100; // 1200

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->getJson(
            route('project.displacements', $project).'?reference='.$referenceMeasurement->id.'&comparison='.$comparisonMeasurement->id
        );

        // asserts a specific structure (partly completed with Copilot autocomplete, then manually edited)
        $response->assertOk()
            ->assertJsonStructure([
                $point->id => ['distance2d', 'distance3d', 'deltaHeight', 'projectedDistance'],
            ]);

        $data = $response->json();
        $pointData = $data[$point->id];

        expect(abs($pointData['distance2d'] - $expected2d))->toBeLessThan(0.001)
            ->and(abs($pointData['distance3d'] - $expected3d))->toBeLessThan(0.001)
            ->and(abs($pointData['deltaHeight'] - $expectedDeltaHeight))->toBeLessThan(0.001)
            ->and(abs($pointData['projectedDistance'] - $expectedProjection))->toBeLessThan(0.001);
    });

    it('returns empty json when points have no values for the selected pair', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();

        $referenceMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2025-01-01 00:00:00',
        ]);

        $comparisonMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2026-01-01 00:00:00',
        ]);

        // Point has no measurement values at all
        Point::factory()->createOne([
            'project_id' => $project->id,
            'is_visible' => true,
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->getJson(
            route('project.displacements', $project).'?reference='.$referenceMeasurement->id.'&comparison='.$comparisonMeasurement->id
        );

        $response->assertOk()
            ->assertExactJson([]);
    });

    it('excludes hidden points from displacement results', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();

        $referenceMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2025-01-01 00:00:00',
        ]);

        $comparisonMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2026-01-01 00:00:00',
        ]);

        $visiblePoint = Point::factory()->createOne([
            'project_id' => $project->id,
            'is_visible' => true,
        ]);

        $hiddenPoint = Point::factory()->createOne([
            'project_id' => $project->id,
            'is_visible' => false,
        ]);

        // Both points have measurement values
        foreach ([$visiblePoint, $hiddenPoint] as $point) {
            MeasurementValue::factory()->createOne([
                'point_id' => $point->id,
                'measurement_id' => $referenceMeasurement->id,
                'addition_id' => null,
            ]);
            MeasurementValue::factory()->createOne([
                'point_id' => $point->id,
                'measurement_id' => $comparisonMeasurement->id,
                'addition_id' => null,
            ]);
        }

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->getJson(
            route('project.displacements', $project).'?reference='.$referenceMeasurement->id.'&comparison='.$comparisonMeasurement->id
        );

        $response->assertOk();
        $data = $response->json();

        // Visible point appears, hidden point does not
        expect($data)->toHaveKey((string) $visiblePoint->id)
            ->and($data)->not->toHaveKey((string) $hiddenPoint->id);
    });

    it('returns null projectedDistance for points without a projection', function () {
        /** @var TestCase $this */
        $project = Project::factory()->createOne();

        $referenceMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2025-01-01 00:00:00',
        ]);

        $comparisonMeasurement = Measurement::factory()->createOne([
            'project_id' => $project->id,
            'measurement_datetime' => '2026-01-01 00:00:00',
        ]);

        $point = Point::factory()->createOne([
            'project_id' => $project->id,
            'projection_id' => null,
            'is_visible' => true,
        ]);

        MeasurementValue::factory()->createOne([
            'point_id' => $point->id,
            'measurement_id' => $referenceMeasurement->id,
            'addition_id' => null,
        ]);

        MeasurementValue::factory()->createOne([
            'point_id' => $point->id,
            'measurement_id' => $comparisonMeasurement->id,
            'addition_id' => null,
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->getJson(
            route('project.displacements', $project).'?reference='.$referenceMeasurement->id.'&comparison='.$comparisonMeasurement->id
        );

        $response->assertOk();
        $data = $response->json();

        expect($data[$point->id]['projectedDistance'])->toBeNull()
            ->and($data[$point->id]['distance2d'])->toBeNumeric()
            ->and($data[$point->id]['distance3d'])->toBeNumeric();
    });
});
