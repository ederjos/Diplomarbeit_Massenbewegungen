<?php

use App\Models\Measurement;
use App\Models\MeasurementValue;
use App\Models\Point;
use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Facades\DB;


test('measurements on project page are loaded chronologically', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->createOne();

    // Create Measurements *out of order*
    $februaryMeasurement = Measurement::factory()->createOne(['project_id' => $project->id, 'measurement_datetime' => '2000-02-01']);
    $januaryMeasurement = Measurement::factory()->createOne(['project_id' => $project->id, 'measurement_datetime' => '2000-01-01']);
    $marchMeasurement = Measurement::factory()->createOne(['project_id' => $project->id, 'measurement_datetime' => '2000-03-01']);

    // Create a Point
    $point = Point::factory()->createOne(['project_id' => $project->id]);

    // Create Values for this point
    MeasurementValue::factory()->createOne(['point_id' => $point->id, 'measurement_id' => $februaryMeasurement->id, 'addition_id' => null]);
    MeasurementValue::factory()->createOne(['point_id' => $point->id, 'measurement_id' => $januaryMeasurement->id, 'addition_id' => null]);
    MeasurementValue::factory()->createOne(['point_id' => $point->id, 'measurement_id' => $marchMeasurement->id, 'addition_id' => null]);

    // Assert that measurement values were created
    $this->assertDatabaseCount('measurement_values', 3);

    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    $response = $this->actingAs($user)->get(route('project', $project));

    $response->assertStatus(200)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Project')
                // Check measurements are ordered
                ->where('measurements.0.id', $januaryMeasurement->id)
                ->where('measurements.1.id', $februaryMeasurement->id)
                ->where('measurements.2.id', $marchMeasurement->id)
                // Check point values are ordered
                ->where('points.0.measurementValues.0.measurementId', $januaryMeasurement->id)
                ->where('points.0.measurementValues.1.measurementId', $februaryMeasurement->id)
                ->where('points.0.measurementValues.2.measurementId', $marchMeasurement->id)
        );
});

test('project details include valid coordinates converted to lat/lon', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->createOne();

    $point = Point::factory()->createOne(['project_id' => $project->id]);

    $measurement = Measurement::factory()->createOne(['project_id' => $project->id]);

    // Converted with https://epsg.io/transform
    $inputX = -28194.450;
    $inputY = 206903.603;
    // Height
    $inputZ = 1774.640;
    $expectedLon = 9.962327;
    $expectedLat = 47.00052;

    MeasurementValue::factory()->createOne([
        'point_id' => $point->id,
        'measurement_id' => $measurement->id,
        'x' => $inputX,
        'y' => $inputY,
        'z' => $inputZ,
        // no addition
        'addition_id' => null,
    ]);

    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    $response = $this->actingAs($user)->get(route('project', $project));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Project')
            // Check if the coordinate transformation (PostGIS) is correct within a small delta
            ->where('points.0.measurementValues.0.lat', fn ($lat) => abs($lat - $expectedLat) < 0.00001)
            ->where('points.0.measurementValues.0.lon', fn ($lon) => abs($lon - $expectedLon) < 0.00001)
    );
});

test('returns error 404 if project doesn\'t exist', function () {
    /** @var \Tests\TestCase $this */

    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    $response = $this->actingAs($user)->get(route('project', ['project' => '12345']));

    $response->assertStatus(404);
});

test('fallback to earliest and latest measurements for reference and comparison measurements', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->createOne();

    // Latest measurement
    $thirdMeasurement = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2026-03-01 00:00:00',
    ]);

    // Some reandom measurement in between
    Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2026-02-01 00:00:00',
    ]);

    // First, oldest and therefore reference measurement
    $firstMeasurement = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2026-01-01 00:00:00',
    ]);

    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    $response = $this->actingAs($user)->get(route('project', $project));

    $response->assertStatus(200)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Project')
                ->where('referenceId', $firstMeasurement->id)
                ->where('comparisonId', $thirdMeasurement->id)
        );
});

test('comparison parameter defaults to latest measurement when invalid', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->createOne();

    // Latest measurement, automatically comparsion if invalid or not provided
    $thirdMeasurement = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2026-03-01 00:00:00',
    ]);

    // Some reandom measurement in between
    Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2026-02-01 00:00:00',
    ]);

    // First, oldest and therefore reference measurement
    Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => '2026-01-01 00:00:00',
    ]);

    /** @var \App\Models\User $user */
    $user = User::factory()->createOne();
    $response = $this->actingAs($user)->get(route('project', $project).'?comparison=not-a-number');

    $response->assertStatus(200)
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Project')
                ->where('comparisonId', $thirdMeasurement->id)
        );
});

test('project calculates average yearly movement correctly', function(){
    $project = Project::factory()->createOne();

    $inputSrid = config('spatial.srids.default');

    $point1 = Point::factory()->createOne(['project_id' => $project->id]);
    $point2 = Point::factory()->createOne(['project_id' => $project->id]);
    $point3 = Point::factory()->createOne(['project_id' => $project->id]);
    $point4 = Point::factory()->createOne(['project_id' => $project->id]);
    $point5 = Point::factory()->createOne(['project_id' => $project->id]);

    // Measurements
    $measurement1 = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => "2020-01-01 00:00:00",
    ]);
    $measurement2 = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => "2021-01-01 00:00:00",
    ]);
    $measurement3 = Measurement::factory()->createOne([
        'project_id' => $project->id,
        'measurement_datetime' => "2025-01-01 00:00:00",
    ]);

    // Point 1 moves 1 meter in year 1. (Average is 1)
    MeasurementValue::factory()->createOne([
        'point_id' => $point1->id,
        'measurement_id' => $measurement1->id,
        'x' => 100,
        'y' => 100,
        'z' => 100,
        'addition_id' => null,
    ]);
    MeasurementValue::factory()->createOne([
        'point_id' => $point1->id,
        'measurement_id' => $measurement2->id,
        'x' => 100,
        'y' => 100,
        'z' => 101,
        'addition_id' => null,
    ]);

    // Point 2 moves 2 meters in year 1, then back by year 5. (Average is 0)
    MeasurementValue::factory()->createOne([
        'point_id' => $point2->id,
        'measurement_id' => $measurement1->id,
        'x' => 200,
        'y' => 200,
        'z' => 100,
        'addition_id' => null,
    ]);
    MeasurementValue::factory()->createOne([
        'point_id' => $point2->id,
        'measurement_id' => $measurement2->id,
        'x' => 202,
        'y' => 200,
        'z' => 100,
        'addition_id' => null,
    ]);
    MeasurementValue::factory()->createOne([
        'point_id' => $point2->id,
        'measurement_id' => $measurement3->id,
        'x' => 200,
        'y' => 200,
        'z' => 100,
        'addition_id' => null,
    ]);
    // Point 3 moves 10 meters in year 1, then another 5 meters by year 5. (Average is 3)
    MeasurementValue::factory()->createOne([
        'point_id' => $point3->id,
        'measurement_id' => $measurement1->id,
        'x' => 300,
        'y' => 1000,
        'z' => 1000,
        'addition_id' => null,
    ]);
    MeasurementValue::factory()->createOne([
        'point_id' => $point3->id,
        'measurement_id' => $measurement2->id,
        'x' => 300,
        'y' => 1010,
        'z' => 1000,
        'addition_id' => null,
    ]);
    MeasurementValue::factory()->createOne([
        'point_id' => $point3->id,
        'measurement_id' => $measurement3->id,
        'x' => 300,
        'y' => 1015,
        'z' => 1000,
        'addition_id' => null,
    ]);
    // Point 4 doesn't move at all. (Average is 0)
    MeasurementValue::factory()->createOne([
        'point_id' => $point4->id,
        'measurement_id' => $measurement1->id,
        'x' => 300,
        'y' => 15,
        'z' => 1000,
        'addition_id' => null,
    ]);
    MeasurementValue::factory()->createOne([
        'point_id' => $point4->id,
        'measurement_id' => $measurement3->id,
        'x' => 300,
        'y' => 15,
        'z' => 1000,
        'addition_id' => null,
    ]);
    // Point 5 only is measured after year 1. (ignore, Average is 0)
    MeasurementValue::factory()->createOne([
        'point_id' => $point5->id,
        'measurement_id' => $measurement2->id,
        'x' => 111,
        'y' => 111,
        'z' => 111,
        'addition_id' => null,
    ]);

    // Calculate average movement
    $averageMovementInCm = $project->averageYearlyMovement();

    // The overall average is 1m per year.
    // The method returns cm/year, so we expect 100.
    $this->assertNotNull($averageMovementInCm);
    $this->assertEqualsWithDelta(100.0, $averageMovementInCm, 0.1);
});