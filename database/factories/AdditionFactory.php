<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Addition>
 */
class AdditionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dx' => fake()->randomFloat(2, -10, 10),
            'dy' => fake()->randomFloat(2, -10, 10),
            'dz' => fake()->randomFloat(2, -10, 10),
        ];
    }
}
