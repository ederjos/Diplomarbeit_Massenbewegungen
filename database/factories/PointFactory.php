<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Projection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Point>
 */
class PointFactory extends Factory
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
            'project_id' => Project::factory(),
            'projection_id' => Projection::factory(),
            'is_visible' => true,
        ];
    }
}
