<?php

use App\Models\Addition;
use App\Models\MeasurementValue;

test('geom is automatically generated when creating measurement value with x y z', function () {
    /** @var MeasurementValue $mv */
    $mv = MeasurementValue::factory()->createOne([
        'x' => 100.1,
        'y' => 200.2,
        'z' => 300.3,
        'addition_id' => null,
    ]);

    expect($mv->geom)->not->toBeNull()
        ->and($mv->geom->getX())->toBe(100.1)
        ->and($mv->geom->getY())->toBe(200.2)
        ->and($mv->geom->getZ())->toBe(300.3);
});

test('geom is automatically updated when updating x y z', function () {
    /** @var MeasurementValue $mv */
    $mv = MeasurementValue::factory()->createOne([
        'x' => 10.1,
        'y' => 20.2,
        'z' => 30.3,
        'addition_id' => null,
    ]);

    // Initial check
    expect($mv->geom->getX())->toBe(10.1);

    $mv->update([
        'x' => 99.9,
    ]);

    // x updated and y & z unchanged
    expect($mv->geom->getX())->toBe(99.9)
        ->and($mv->geom->getY())->toBe(20.2)
        ->and($mv->geom->getZ())->toBe(30.3);
});

test('geom is refreshed when a linked addition changes its offsets', function () {

    /** @var Addition $addition */
    $addition = Addition::factory()->createOne([
        'dx' => 0.0,
        'dy' => 0.0,
        'dz' => 0.0,
    ]);

    /** @var MeasurementValue $mv */
    $mv = MeasurementValue::factory()->createOne([
        'addition_id' => $addition->id,
        'x' => 10.0,
        'y' => 20.0,
        'z' => 30.0,
    ]);

    // Currently relies on geom being of SRID 31254
    expect($mv->geom->getX())->toBe(10.0)
        ->and($mv->geom->getY())->toBe(20.0)
        ->and($mv->geom->getZ())->toBe(30.0);

    $addition->update([
        'dx' => 1.5,
        'dy' => -0.5,
        'dz' => 0.25,
    ]);

    $mv->refresh();

    expect($mv->geom->getX())->toBe(11.5)
        ->and($mv->geom->getY())->toBe(19.5)
        ->and($mv->geom->getZ())->toBe(30.25);
});

test('geom is computed with addition offsets on creation', function () {
    /** @var Addition $addition */
    $addition = Addition::factory()->createOne([
        'dx' => 2.0,
        'dy' => -1.0,
        'dz' => 0.5,
    ]);

    /** @var MeasurementValue $mv */
    $mv = MeasurementValue::factory()->createOne([
        'addition_id' => $addition->id,
        'x' => 10.0,
        'y' => 20.0,
        'z' => 30.0,
    ]);

    expect($mv->geom->getX())->toBe(12.0)
        ->and($mv->geom->getY())->toBe(19.0)
        ->and($mv->geom->getZ())->toBe(30.5);
});
