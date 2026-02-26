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
            'is_admin' => fake()->boolean(),
            'can_manage_projects' => fake()->boolean(),
            'can_manage_measurements' => fake()->boolean(),
            'can_comment' => fake()->boolean(),
        ];
    }
}
