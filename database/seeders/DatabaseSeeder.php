<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /**
         * ChatGPT GPT-5 mini, 2025-11-18
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
            // PointsTableSeeder::class
            // Points are now created dynamically in MeasurementValuesTableSeeder
            MeasurementsTableSeeder::class,
            AdditionsTableSeeder::class,
            MeasurementValuesTableSeeder::class,
            ProjectionsTableSeeder::class,
            CommentsTableSeeder::class,
        ]);
    }
}
