<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeasurementsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('measurements')->insert([
            [
                'id' => 1,
                'name' => 'NM',
                'measurement_datetime' => '2005-09-23 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'FM1',
                'measurement_datetime' => '2006-07-03 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'FM2',
                'measurement_datetime' => '2006-10-02 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'FM3',
                'measurement_datetime' => '2007-10-03 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => 'FM4',
                'measurement_datetime' => '2008-06-09 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'name' => 'FM5',
                'measurement_datetime' => '2008-07-02 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'name' => 'FM6',
                'measurement_datetime' => '2008-08-18 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'name' => 'FM7',
                'measurement_datetime' => '2008-08-29 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 9,
                'name' => 'FM8',
                'measurement_datetime' => '2008-10-20 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 10,
                'name' => 'FM9',
                'measurement_datetime' => '2008-11-10 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 11,
                'name' => 'FM10',
                'measurement_datetime' => '2009-06-18 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 12,
                'name' => 'FM11',
                'measurement_datetime' => '2009-10-29 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 13,
                'name' => 'FM12',
                'measurement_datetime' => '2010-07-07 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 14,
                'name' => 'FM13',
                'measurement_datetime' => '2011-06-20 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 15,
                'name' => 'FM14',
                'measurement_datetime' => '2012-07-17 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 16,
                'name' => 'FM15',
                'measurement_datetime' => '2013-06-16 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 17,
                'name' => 'FM16',
                'measurement_datetime' => '2014-06-13 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 18,
                'name' => 'FM17',
                'measurement_datetime' => '2015-06-12 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 19,
                'name' => 'FM18',
                'measurement_datetime' => '2016-06-22 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 20,
                'name' => 'FM19',
                'measurement_datetime' => '2017-06-08 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 21,
                'name' => 'FM20',
                'measurement_datetime' => '2018-06-06 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 22,
                'name' => 'FM21',
                'measurement_datetime' => '2019-06-17 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 23,
                'name' => 'FM22',
                'measurement_datetime' => '2020-06-13 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 24,
                'name' => 'FM23',
                'measurement_datetime' => '2021-06-15 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 25,
                'name' => 'FM24',
                'measurement_datetime' => '2022-06-03 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 26,
                'name' => 'FM25',
                'measurement_datetime' => '2023-06-01 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 27,
                'name' => 'FM26',
                'measurement_datetime' => '2024-05-29 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 28,
                'name' => 'FM27',
                'measurement_datetime' => '2025-06-04 00:00:00',
                'project_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
