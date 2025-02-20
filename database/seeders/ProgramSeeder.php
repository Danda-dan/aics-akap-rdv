<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            ['id' => 1, 'name' => 'ECT', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'AICS', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('programs')->insert($programs);
    }
}
