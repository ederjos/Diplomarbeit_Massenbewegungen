<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
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
            'is_admin' => false,
            'can_view_detailed_data' => false,
            'can_manage_projects' => false,
            'can_manage_measurements' => false,
            'can_comment' => false,
        ];
    }
}
