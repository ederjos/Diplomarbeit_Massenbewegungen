<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClerksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clerks')->insert([
            'id' => 1,
            'name' => 'Sachbearbeiter Kürzel',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
