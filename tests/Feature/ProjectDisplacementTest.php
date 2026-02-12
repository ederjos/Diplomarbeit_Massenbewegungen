<?php

use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\Point;
use App\Models\Project;
use App\Models\Projection;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('project page exposes displacement values for the selected comparison epoch', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->createOne();

    $firstMeasurement = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2025-01-01 00:00:00',
    ]);

    $comparisonMeasurement = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2025-02-01 00:00:00',
    ]);

    $lastMeasurement = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2025-03-01 00:00:00',
    ]);

    $project->fill(['reference_measurement_id' => $firstMeasurement->id])->save();

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
    $comparisonCoords = [103.0, 204.0, 312.0]; // dX=3, dY=4, dZ=12
    $lastCoords = [110.0, 200.0, 290.0];

    // First measurement
    MeasurementValue::factory()->createOne([
        'point_id' => $point->id,
        'measurement_id' => $firstMeasurement->id,
        'x' => $referenceCoords[0],
        'y' => $referenceCoords[1],
        'z' => $referenceCoords[2],
        'addition_id' => null,
    ]);

    // Second, comparison measurement
    MeasurementValue::factory()->createOne([
        'point_id' => $point->id,
        'measurement_id' => $comparisonMeasurement->id,
        'x' => $comparisonCoords[0],
        'y' => $comparisonCoords[1],
        'z' => $comparisonCoords[2],
        'addition_id' => null,
    ]);

    // Third, last measurement (should be ignored)
    MeasurementValue::factory()->createOne([
        'point_id' => $point->id,
        'measurement_id' => $lastMeasurement->id,
        'x' => $lastCoords[0],
        'y' => $lastCoords[1],
        'z' => $lastCoords[2],
        'addition_id' => null,
    ]);

    /**
     * GPT-5.2-Codex, 2026-02-12
     * "Write this test so that it checks if the displacements were calculated correctly"
     */

    // Calculate expected displacement values
    $expectedDx = $comparisonCoords[0] - $referenceCoords[0];
    $expectedDy = $comparisonCoords[1] - $referenceCoords[1];
    $expectedDz = $comparisonCoords[2] - $referenceCoords[2];

    $expected2d = sqrt($expectedDx ** 2 + $expectedDy ** 2) * 100; // meters -> centimeters
    $expected3d = sqrt($expectedDx ** 2 + $expectedDy ** 2 + $expectedDz ** 2) * 100;
    $expectedProjection = $expectedDx * 100; // axis is (1,0)
    $expectedDeltaHeight = $expectedDz * 100;

    /** @var User $user */
    $user = User::factory()->createOne();
    $response = $this->actingAs($user)->get(route('project', $project).'?comparison='.$comparisonMeasurement->id);

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Project')
            ->where('comparisonId', $comparisonMeasurement->id)
            ->where("displacements.{$point->id}.distance2d", fn ($value) => abs($value - $expected2d) < 0.001)
            ->where("displacements.{$point->id}.distance3d", fn ($value) => abs($value - $expected3d) < 0.001)
            ->where("displacements.{$point->id}.projectedDistance", fn ($value) => abs($value - $expectedProjection) < 0.001)
            ->where("displacements.{$point->id}.deltaHeight", fn ($value) => abs($value - $expectedDeltaHeight) < 0.001)
        );
});

test('displacement calculations skip points without comparison values', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->createOne();

    $referenceMeasurement = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2025-01-01 00:00:00',
    ]);

    $comparisonMeasurement = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2026-01-01 00:00:00',
    ]);

    $project->fill(['reference_measurement_id' => $referenceMeasurement->id])->save();

    $pointWithData = Point::factory()->createOne([
        'project_id' => $project->id,
        'is_visible' => true,
    ]);

    $pointWithoutComparison = Point::factory()->createOne([
        'project_id' => $project->id,
        'is_visible' => true,
    ]);

    $pointWithoutReference = Point::factory()->createOne([
        'project_id' => $project->id,
        'is_visible' => true,
    ]);

    // Point 1 has values for both measurements
    MeasurementValue::factory()->createOne([
        'point_id' => $pointWithData->id,
        'measurement_id' => $referenceMeasurement->id,
    ]);

    MeasurementValue::factory()->createOne([
        'point_id' => $pointWithData->id,
        'measurement_id' => $comparisonMeasurement->id,
    ]);

    // Point 2 only has the reference value
    MeasurementValue::factory()->createOne([
        'point_id' => $pointWithoutComparison->id,
        'measurement_id' => $referenceMeasurement->id,
    ]);

    // Point 3 only has the comparison value
    MeasurementValue::factory()->createOne([
        'point_id' => $pointWithoutReference->id,
        'measurement_id' => $comparisonMeasurement->id,
    ]);

    /** @var User $user */
    $user = User::factory()->createOne();
    $project->users()->attach($user, ['is_contact_person' => true]);

    /**
     * Gemini 2.5 Pro, 2026-02-12
     * "Perform this test now to confirm the missing data."
     */
    $this->actingAs($user)
        ->get(route('project', $project).'?comparison='.$comparisonMeasurement->id)
        ->assertInertia(fn (Assert $page) => $page
            ->has('displacements', 1)
            ->where("displacements.{$pointWithData->id}.distance2d", fn ($value) => is_numeric($value))
            ->missing("displacements.{$pointWithoutComparison->id}")
            ->missing("displacements.{$pointWithoutReference->id}")
        );
});
