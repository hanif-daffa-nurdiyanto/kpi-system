<?php

namespace Database\Factories;

use App\Models\KpiDailyEntry;
use App\Models\KpiMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KpiEntryDetail>
 */
class KpiEntryDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => KpiDailyEntry::factory(),
            'metric_id' => KpiMetric::inRandomOrder()->first()?->id,
            'value' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
