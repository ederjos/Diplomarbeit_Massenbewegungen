<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;

use App\Models\Point;
use App\Models\Measurement;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeasurementValue>
 */
class MeasurementValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $x = fake()->randomFloat(2, -61700, 525700);
        $y = fake()->randomFloat(2, 140000, 453900);
        $z = fake()->randomFloat(2, 100, 3000);

        return [
            'x' => $x,
            'y' => $y,
            'z' => $z,
            'geom' => MagellanPoint::make($x, $y, $z, srid:31254),
            'point_id' => Point::factory(),
            'measurement_id' => Measurement::factory(),
            'addition_id' => null // Like projection: implement later
        ];
    }
}
