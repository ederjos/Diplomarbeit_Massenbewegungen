<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::findOrFail(1)->users()->attach(1, [
            'is_contact_person' => true,
            'is_favorite' => false,
        ]);
    }
}
