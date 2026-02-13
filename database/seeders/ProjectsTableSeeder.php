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
            'client_id' => 1,
            'clerk_id' => 1,
            'type_id' => 1,
            'municipality_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'projects_id_seq\', (SELECT MAX(id) FROM projects));');
        }
    }
}
