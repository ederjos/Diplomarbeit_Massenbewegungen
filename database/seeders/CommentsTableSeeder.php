<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Comment::fillAndInsert([
            'content' => 'Diese Messung ist die aktuellste Messung.',
            'measurement_id' => 28,
            'user_id' => 1,
            'created_at' => '2025-09-17 15:15:00',
        ]);
    }
}
