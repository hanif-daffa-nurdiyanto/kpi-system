<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->superAdmin()->create();
        User::factory()->manager()->count(5)->create();
        User::factory()->employee()->count(20)->create();
    }
}
