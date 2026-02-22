<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'manage_users' => true,
                'manage_projects' => true,
                'manage_measurements' => true,
                'manage_comments' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'id' => 2,
                'name' => 'expert', // not sure about the name
                'manage_users' => false,
                'manage_projects' => false,
                'manage_measurements' => false, // not sure about this either, but it can be changed in the admin page (once it's implemented)
                'manage_comments' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'id' => 3,
                'name' => 'guest',
                'manage_users' => false,
                'manage_projects' => false,
                'manage_measurements' => false,
                'manage_comments' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // update autoincrement value (this doesn't happen automatically when inserting with an id)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'roles_id_seq\', (SELECT MAX(id) FROM roles));');
        }
    }
}
