<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Client;
use App\Models\Clerk;
use App\Models\Type;
use App\Models\Municipality;

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
            // 'period' => '2 months', // -> postgre intervals are not supported by faker
            'client_id' => Client::factory(),
            'clerk_id' => Clerk::factory(),
            'type_id' => Type::factory(),
            'municipality_id' => Municipality::factory(),
        ];
    }
}
