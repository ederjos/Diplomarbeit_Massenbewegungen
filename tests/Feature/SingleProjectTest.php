<?php

use App\Models\Project;
use App\Models\Measurement;
use App\Models\Point;
use App\Models\MeasurementValue;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;

pest()->use(RefreshDatabase::class);

test('measurements on project page are loaded chronologically', function () {
    /** @var \Tests\TestCase $this */
    $project = Project::factory()->create();

    // Create Measurements *out of order*
    $februaryMeasurement = Measurement::factory()->create(['project_id' => $project->id, 'measurement_datetime' => '2000-02-01']);
    $januaryMeasurement = Measurement::factory()->create(['project_id' => $project->id, 'measurement_datetime' => '2000-01-01']);
    $marchMeasurement = Measurement::factory()->create(['project_id' => $project->id, 'measurement_datetime' => '2000-03-01']);

    // Create a Point
    $point = Point::factory()->create(['project_id' => $project->id]);

    // Create Values for this point
    MeasurementValue::factory()->create(['point_id' => $point->id, 'measurement_id' => $februaryMeasurement->id]);
    MeasurementValue::factory()->create(['point_id' => $point->id, 'measurement_id' => $januaryMeasurement->id]);
    MeasurementValue::factory()->create(['point_id' => $point->id, 'measurement_id' => $marchMeasurement->id]);

    // Assert that measurement values were created
    $this->assertDatabaseCount('measurement_values', 3);

    $response = $this->get(route('project', $project));

    $response->assertStatus(200)
        ->assertInertia(
            fn(Assert $page) => $page
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
    $project = Project::factory()->create();

    $point = Point::factory()->create(['project_id' => $project->id]);
    
    $measurement = Measurement::factory()->create(['project_id' => $project->id]);

    // Converted with https://epsg.io/transform
    $inputX = -28194.450;
    $inputY = 206903.603;
    $inputZ = 1774.640; // Height
    $expectedLon = 9.962327;
    $expectedLat = 47.00052;

    MeasurementValue::factory()->create([
        'point_id' => $point->id,
        'measurement_id' => $measurement->id,
        'x' => $inputX,
        'y' => $inputY,
        'z' => $inputZ,
        // Otherwise, the factory uses random numbers for geom
        'geom' => MagellanPoint::make($inputX, $inputY, $inputZ, srid: 31254)
    ]);

    $response = $this->get(route('project', $project));

    $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Project')
                // Check if the coordinate transformation (PostGIS) is correct within a small delta
                ->where('points.0.measurementValues.0.lat', fn($lat) => abs($lat - $expectedLat) < 0.00001)
                ->where('points.0.measurementValues.0.lon', fn($lon) => abs($lon - $expectedLon) < 0.00001)
        );
});

test('returns error 404 if project doesn\'t exist', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get(route('project', ['project' => '12345']));

    $response->assertStatus(404);
});