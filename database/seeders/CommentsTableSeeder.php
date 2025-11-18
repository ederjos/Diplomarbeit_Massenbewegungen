<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('comments')->insert([
            'id' => 1,
            'content' => '',
            'measurement_id' => 28,
            'user_id' => 1,
            'created_at' => '2025-09-17 15:15:00', // comment datetime
            'updated_at' => now()
        ]);
    }
}
