<?php

namespace Database\Factories;

use App\Models\Addition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Addition>
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
