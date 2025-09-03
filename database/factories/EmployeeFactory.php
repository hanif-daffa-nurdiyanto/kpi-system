<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\KpiDailyEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Employee::class;

    protected static array $availableUsers = [];

    public function definition(): array
    {
        if (empty(static::$availableUsers)) {
            static::$availableUsers = User::whereHas('roles', fn($q) => $q->where('name', 'employee'))
                ->pluck('id')
                ->shuffle()
                ->toArray();
        }

        return [
            'user_id' => array_shift(static::$availableUsers) ?? User::factory(),
            'phone' => $this->faker->phoneNumber(),
            'department_id' => Department::inRandomOrder()->first()?->id ?? Department::factory(),
        ];
    }
}
