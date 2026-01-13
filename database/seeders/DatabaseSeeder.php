<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */

    // Reset the entire db:
    // php artisan migrate:fresh --seed

    // Only load the test data:
    // php artisan db:seed

    public function run(): void
    {
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        /* Prompt (ChatGPT GPT-5 mini)
         * "now that all data is written in the seeders, how do i import it into the database?"
         */

        // Tells laravel to execute another seeder class
        $this->call([ // Order matters!
            MunicipalitiesTableSeeder::class, // Returns class name as string (including its location)
            ClientsTableSeeder::class,
            TypesTableSeeder::class,
            ClerksTableSeeder::class,
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            ProjectsTableSeeder::class,
            ProjectUserSeeder::class,
            ProjectionsTableSeeder::class,
            // PointsTableSeeder::class, // Points are now created dynamically in MeasurementValuesTableSeeder
            MeasurementsTableSeeder::class,
            AdditionsTableSeeder::class,
            MeasurementValuesTableSeeder::class,
            CommentsTableSeeder::class
        ]);
    }
}
