<?php

namespace Database\Seeders;

use App\Models\KpiCategory;
use App\Models\KpiMetric;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KpiMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kpiMetrics = [
            'Sales' => [
                ['name' => 'Outbound Calls', 'unit' => 'count', 'target' => 150],
                ['name' => 'Talk Time', 'unit' => 'hours', 'target' => 3],
                ['name' => 'Auto Quotes', 'unit' => 'count', 'target' => 6],
                ['name' => 'Households Sold', 'unit' => 'count', 'target' => 1],
                ['name' => 'Allstate Items Sold', 'unit' => 'count', 'target' => 2],
                ['name' => 'Allstate Premium Sold', 'unit' => 'dollars', 'target' => 1000],
            ],
            'Customer Service' => [
                ['name' => 'Customer Satisfaction Score', 'unit' => 'percentage', 'target' => 85],
                ['name' => 'First Call Resolution', 'unit' => 'percentage', 'target' => 90],
                ['name' => 'Average Handle Time', 'unit' => 'minutes', 'target' => 5],
                ['name' => 'Net Promoter Score', 'unit' => 'score', 'target' => 70],
            ],
            'Marketing' => [
                ['name' => 'Ad Click-Through Rate', 'unit' => 'percentage', 'target' => 5],
                ['name' => 'Lead Conversion Rate', 'unit' => 'percentage', 'target' => 10],
                ['name' => 'Social Media Engagement', 'unit' => 'count', 'target' => 1000],
            ],
            'Operations' => [
                ['name' => 'Order Fulfillment Time', 'unit' => 'hours', 'target' => 24],
                ['name' => 'Production Efficiency', 'unit' => 'percentage', 'target' => 90],
            ],
            'Finance' => [
                ['name' => 'Revenue Growth', 'unit' => 'percentage', 'target' => 10],
                ['name' => 'Expense Reduction', 'unit' => 'percentage', 'target' => 5],
            ],
        ];

        // Delete existing data untuk menghindari duplikasi
        KpiMetric::query()->delete();

        // Loop through each category
        foreach ($kpiMetrics as $categoryName => $metrics) {
            // Temukan kategori yang sesuai
            $category = KpiCategory::where('name', $categoryName . ' KPI')->first();

            // Jika kategori tidak ditemukan, lanjutkan ke iterasi berikutnya
            if (!$category) {
                continue;
            }

            // Buat metrik untuk kategori ini
            foreach ($metrics as $metricData) {
                KpiMetric::create([
                    'kpi_category_id' => $category->id,
                    'name' => $metricData['name'],
                    'description' => "This metric tracks {$metricData['name']} for {$categoryName}.",
                    'unit' => $metricData['unit'],
                    'target_value' => $metricData['target'],
                    'weight' => rand(10, 100) / 100,
                    'is_higher_better' => true,
                    'is_active' => true,
                ]);
            }
        }
    }
}
