<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
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
            'is_active' => fake()->boolean(),
            'comment' => fake()->sentence(),
            'last_file_number' => fake()->randomNumber(4),
            // -> postgre intervals are not supported by faker, just use default
            'client' => fake()->company(),
            'clerk' => fake()->name(),
            'type' => fake()->word(),
            'municipality' => fake()->city(),
        ];
    }
}
