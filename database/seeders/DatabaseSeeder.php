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
        $this->call([
            // Order matters!
            // Returns class name as string (including its location)
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            ClientsTableSeeder::class,
            ClerksTableSeeder::class,
            TypesTableSeeder::class,
            MunicipalitiesTableSeeder::class,
            ProjectsTableSeeder::class,
            ProjectUserSeeder::class,
            // Points are now created dynamically in MeasurementValuesTableSeeder
            // PointsTableSeeder::class,
            MeasurementsTableSeeder::class,
            MeasurementValuesTableSeeder::class,
            ProjectionsTableSeeder::class,
            AdditionsTableSeeder::class,
            CommentsTableSeeder::class,
        ]);
    }
}
