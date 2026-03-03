<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::fillAndInsert([
            'name' => 'Josef Eder',
            'email' => 'josef.eder@student.htl-rankweil.at',
            // A long password like "correct-horse-battery-staple" would be better, but this is just for testing
            'password' => Hash::make('secret'),
            'role_id' => 1,
        ]);
    }
}
