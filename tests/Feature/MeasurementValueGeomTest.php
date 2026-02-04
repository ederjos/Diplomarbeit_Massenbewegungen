<?php

use App\Models\MeasurementValue;

test('geom is automatically generated when creating measurement value with x y z', function () {
    /** @var MeasurementValue $mv */
    $mv = MeasurementValue::factory()->createOne([
        'x' => 100.1,
        'y' => 200.2,
        'z' => 300.3,
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
