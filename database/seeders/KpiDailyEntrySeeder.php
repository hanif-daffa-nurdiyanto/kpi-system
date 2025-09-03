<?php

namespace Database\Seeders;

use App\Models\KpiDailyEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KpiDailyEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KpiDailyEntry::factory()->count(50)->create();
    }
}
