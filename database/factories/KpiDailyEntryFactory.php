<?php

namespace Database\Factories;

use App\Models\KpiDailyEntry;
use App\Models\KpiEntryDetail;
use App\Models\KpiMetric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KpiDailyEntry>
 */
class KpiDailyEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Tentukan tanggal secara acak di bulan ini atau bulan lalu
        $entryDate = fake()->boolean(50)
            ? now()->subMonth()->startOfMonth()->addDays(rand(0, 29))
            : now()->startOfMonth()->addDays(rand(0, now()->day - 1));

        $status = fake()->randomElement(['draft', 'approved']);

        return [
            'user_id' => User::has('employee')->inRandomOrder()->first()?->id,
            'entry_date' => $entryDate,
            'submitted_at' => $status === 'draft' ? null : $entryDate,
            'status' => $status,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (KpiDailyEntry $entry) {
            KpiEntryDetail::factory()->count(rand(1, 5))->create([
                'entry_id' => $entry->id,
                'metric_id' => KpiMetric::inRandomOrder()->first()?->id,
                'value' => fake()->randomFloat(2, 0, 100),
            ]);
        });
    }
}
