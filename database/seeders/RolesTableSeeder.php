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
                'is_admin' => true,
                'can_manage_projects' => true,
                'can_manage_measurements' => true,
                'can_comment' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'id' => 2,
                'name' => 'expert', // not sure about the name
                'is_admin' => false,
                'can_manage_projects' => false,
                'can_manage_measurements' => false, // not sure about this either, but it can be changed in the admin page (once it's implemented)
                'can_comment' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'id' => 3,
                'name' => 'guest',
                'is_admin' => false,
                'can_manage_projects' => false,
                'can_manage_measurements' => false,
                'can_comment' => false,
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
