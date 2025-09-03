<?php

namespace Database\Seeders;

use App\Models\KpiCategory;
use App\Models\KpiMetric;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KpiCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Sales' => 'KPI yang berfokus pada performa penjualan.',
            'Customer Service' => 'KPI untuk mengevaluasi kepuasan pelanggan.',
            'Marketing' => 'KPI terkait performa iklan dan branding.',
            'Operations' => 'KPI untuk efisiensi operasional.',
            'Finance' => 'KPI untuk manajemen keuangan perusahaan.',
        ];

        foreach ($categories as $name => $description) {
            KpiCategory::firstOrCreate(
                ['name' => $name . " KPI"],
                [
                    'description' => $description,
                    'applicable_roles' => ['manager', 'employee'],
                    'is_active' => true,
                ]
            );
        }
    }
}
