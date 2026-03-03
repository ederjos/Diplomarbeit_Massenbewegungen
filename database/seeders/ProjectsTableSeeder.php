<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('projects')->insert([
            'id' => 1,
            'name' => 'Mäßtobel',
            'is_active' => true,
            'comment' => 'Dies ist das Testprojekt für die Entwicklung.',
            'last_file_number' => 7273,
            'period' => '2 months 14 days',
            'movement_magnitude' => '3 cm/Jahr in den letzten zwei Jahren',
            'client_id' => 1,
            'clerk_id' => 1,
            'type_id' => 1,
            'municipality_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            /**
             * Claude 4.6 Sonnet, 2026-03-03
             * "Please fix the image insert in the seeder and the display in ProjectController."
             */
            // PostgreSQL requires binary data to be hex-encoded for raw insert into bytea
            'image' => DB::raw("decode('".bin2hex(file_get_contents(public_path('apple-touch-icon.png')))."', 'hex')"),
            'image_mime_type' => 'image/png',
        ]);

        // update autoincrement value (this doesn't happen automatically when inserting with an id)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'projects_id_seq\', (SELECT MAX(id) FROM projects));');
        }
    }
}
