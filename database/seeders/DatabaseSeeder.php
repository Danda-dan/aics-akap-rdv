<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'program_id' => 2,
            'position_id' => 1,
            'usertype' => '',
            'poo' => '',
            'name' => 'RDV Focal2',
            'email' => 'rdv_focal2@dswd.gov.ph',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(60),
        ]);
    }
}
