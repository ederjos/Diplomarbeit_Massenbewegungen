<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projection>
 */
class ProjectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Make the vector ax and ay to have a length of exactly 1.
        // This is impossible if both ax and ay are 0
        do {
            $ax = fake()->randomFloat(7, -1, 1);
            $ay = fake()->randomFloat(7, -1, 1);
            $length = sqrt(($ax * $ax) + ($ay * $ay));
        } while ($length == 0.0);

        $ax /= $length;
        $ay /= $length;
        return [
            'ax' => $ax,
            'ay' => $ay,
        ];
    }
}
