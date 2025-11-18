<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeasurementValuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $testData = [
            // NM
            [
                'x' => -28194.450,
                'y' => 206903.603,
                'z' => 1774.640,
                'point_id' => 1,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28276.711,
                'y' => 206990.782,
                'z' => 1668.540,
                'point_id' => 2,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28333.863,
                'y' => 206922.954,
                'z' => 1691.034,
                'point_id' => 3,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28380.236,
                'y' => 206889.103,
                'z' => 1697.406,
                'point_id' => 4,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28308.146,
                'y' => 206868.297,
                'z' => 1755.397,
                'point_id' => 5,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28336.592,
                'y' => 206824.262,
                'z' => 1763.889,
                'point_id' => 6,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28325.472,
                'y' => 206808.192,
                'z' => 1770.431,
                'point_id' => 7,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28279.799,
                'y' => 206820.103,
                'z' => 1796.332,
                'point_id' => 8,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28242.226,
                'y' => 206847.746,
                'z' => 1815.816,
                'point_id' => 9,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29819.417,
                'y' => 208233.175,
                'z' => 1528.962,
                'point_id' => 10,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29802.352,
                'y' => 208255.442,
                'z' => 1525.811,
                'point_id' => 11,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29772.190,
                'y' => 208297.947,
                'z' => 1521.839,
                'point_id' => 12,
                'measurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // FM1
            [
                'x' => -28194.469,
                'y' => 206903.589,
                'z' => 1774.645,
                'point_id' => 1,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28276.748,
                'y' => 206990.814,
                'z' => 1668.520,
                'point_id' => 2,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28333.902,
                'y' => 206923.016,
                'z' => 1690.997,
                'point_id' => 3,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28380.264,
                'y' => 206889.142,
                'z' => 1697.381,
                'point_id' => 4,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28308.195,
                'y' => 206868.358,
                'z' => 1755.340,
                'point_id' => 5,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28336.611,
                'y' => 206824.254,
                'z' => 1763.910,
                'point_id' => 6,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28325.485,
                'y' => 206808.184,
                'z' => 1770.438,
                'point_id' => 7,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28279.812,
                'y' => 206820.101,
                'z' => 1796.343,
                'point_id' => 8,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28242.243,
                'y' => 206847.733,
                'z' => 1815.814,
                'point_id' => 9,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29819.415,
                'y' => 208233.177,
                'z' => 1528.963,
                'point_id' => 10,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29802.354,
                'y' => 208255.443,
                'z' => 1525.817,
                'point_id' => 11,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29772.190,
                'y' => 208297.948,
                'z' => 1521.840,
                'point_id' => 12,
                'measurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // FM2
            [
                'x' => -28194.443,
                'y' => 206903.628,
                'z' => 1774.649,
                'point_id' => 1,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28276.744,
                'y' => 206990.842,
                'z' => 1668.516,
                'point_id' => 2,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28333.880,
                'y' => 206923.075,
                'z' => 1691.012,
                'point_id' => 3,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28380.248,
                'y' => 206889.183,
                'z' => 1697.387,
                'point_id' => 4,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28308.211,
                'y' => 206868.374,
                'z' => 1755.317,
                'point_id' => 5,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28336.587,
                'y' => 206824.285,
                'z' => 1763.914,
                'point_id' => 6,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28325.473,
                'y' => 206808.206,
                'z' => 1770.441,
                'point_id' => 7,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28279.795,
                'y' => 206820.129,
                'z' => 1796.351,
                'point_id' => 8,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -28242.218,
                'y' => 206847.772,
                'z' => 1815.822,
                'point_id' => 9,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29819.415,
                'y' => 208233.176,
                'z' => 1528.965,
                'point_id' => 10,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29802.355,
                'y' => 208255.442,
                'z' => 1525.820,
                'point_id' => 11,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'x' => -29772.192,
                'y' => 208297.949,
                'z' => 1521.841,
                'point_id' => 12,
                'measurement_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('measurement_values')->insert($testData);
    }
}
