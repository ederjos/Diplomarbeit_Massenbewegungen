<?php

namespace Database\Seeders;

use App\Models\Clerk;
use Illuminate\Database\Seeder;

class ClerksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clerk::fillAndInsert([
            'name' => 'Sachbearbeiter Kürzel',
        ]);
    }
}
