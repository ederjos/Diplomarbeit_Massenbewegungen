<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('project_user')->insert([
            'id' => 1,
            'is_contact_person' => true,
            'project_id' => 1,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'project_user_id_seq\', (SELECT MAX(id) FROM project_user));');
        }
    }
}
