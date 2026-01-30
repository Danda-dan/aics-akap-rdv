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
            'program_id' => 3,
            'position_id' => 1,
            'usertype' => '',
            'poo' => '',
            'name' => 'SLP User',
            'email' => 'slp@user.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(60),
        ]);
    }
}
