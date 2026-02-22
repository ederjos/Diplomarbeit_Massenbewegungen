<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Josef Eder',
            'email' => 'josef.eder@student.htl-rankweil.at',
            'password' => Hash::make('secret'),
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // update autoincrement value (this doesn't happen automatically when inserting with an id)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'users_id_seq\', (SELECT MAX(id) FROM users));');
        }
    }
}
