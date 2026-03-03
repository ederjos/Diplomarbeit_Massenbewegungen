<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::fillAndInsert([
            [
                'name' => 'admin',
                'is_admin' => true,
                'can_manage_projects' => true,
                'can_manage_measurements' => true,
                'can_comment' => true,
            ], [
                'name' => 'expert', // not sure about the name
                'is_admin' => false,
                'can_manage_projects' => false,
                'can_manage_measurements' => false, // not sure about this either, but it can be changed in the admin page (once it's implemented)
                'can_comment' => true,
            ], [
                'name' => 'guest',
                'is_admin' => false,
                'can_manage_projects' => false,
                'can_manage_measurements' => false,
                'can_comment' => false,
            ],
        ]);
    }
}
