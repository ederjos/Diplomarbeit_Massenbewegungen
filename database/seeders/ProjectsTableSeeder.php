<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::fillAndInsert([
            'name' => 'Mäßtobel',
            'is_active' => true,
            'comment' => 'Dies ist das Testprojekt für die Entwicklung.',
            'last_file_number' => 7273,
            'measurement_interval' => 'Alle 6 Monate',
            'movement_magnitude' => '3 cm/Jahr in den letzten zwei Jahren',
            /**
             * Claude 4.6 Sonnet, 2026-03-03
             * "Please fix the image insert in the seeder and the display in ProjectController."
             */
            // PostgreSQL requires binary data to be hex-encoded for raw insert into bytea
            'image' => DB::raw("decode('".bin2hex(file_get_contents(public_path('apple-touch-icon.png')))."', 'hex')"),
            'image_mime_type' => 'image/png',
            'client' => 'Auftraggeber Kürzel',
            'clerk' => 'Sachbearbeiter Kürzel',
            'type' => 'Hang',
            'municipality' => 'St. Gallenkirch',
        ]);
    }
}
