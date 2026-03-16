<?php

namespace Database\Factories;

use App\Models\Measurement;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Measurement>
 */
class MeasurementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'measurement_datetime' => fake()->dateTime(),
            'project_id' => Project::factory(),
        ];
    }
}
