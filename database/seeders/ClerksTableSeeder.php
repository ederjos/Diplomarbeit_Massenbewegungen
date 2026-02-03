<?php

namespace Database\Seeders;

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
            'updated_at' => now(),
        ]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('clerks_id_seq', (SELECT MAX(id) FROM clerks));");
        }
    }
}
