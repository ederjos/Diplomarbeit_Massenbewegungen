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
                'can_add' => true,
                'can_edit' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'id' => 2,
                'name' => 'guest',
                'can_add' => false,
                'can_edit' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'roles_id_seq\', (SELECT MAX(id) FROM roles));');
        }
    }
}
