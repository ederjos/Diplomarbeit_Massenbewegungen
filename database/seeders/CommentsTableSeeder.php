<?php

namespace Database\Seeders;

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
            'content' => 'Diese Messung ist die aktuellste Messung.',
            'measurement_id' => 28,
            'user_id' => 1,
            // comment datetime
            'created_at' => '2025-09-17 15:15:00',
            'updated_at' => now(),
        ]);

        // update autoincrement value (this doesn't happen automatically when inserting with an id)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SELECT setval(\'comments_id_seq\', (SELECT MAX(id) FROM comments));');
        }
    }
}
