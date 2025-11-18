<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('points')->insert([
            [
                'id' => 1,
                'name' => 'PP1',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'PP2',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'PP3',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'PP4',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => 'PP5',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'name' => 'PP6',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'name' => 'PP7',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'name' => 'PP8',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 9,
                'name' => 'PP9',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 10,
                'name' => 'PP201',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 11,
                'name' => 'PP202',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 12,
                'name' => 'PP203',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
