<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $position = [
            ['id' => 1, 'name' => 'Project Development Officer  II', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Social Welfare Officer II', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('positions')->insert($position);
    }
}
