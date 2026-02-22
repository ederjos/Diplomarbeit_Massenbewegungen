<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->jobTitle(),
            'manage_users' => fake()->boolean(),
            'manage_projects' => fake()->boolean(),
            'manage_measurements' => fake()->boolean(),
            'manage_comments' => fake()->boolean(),
        ];
    }
}
