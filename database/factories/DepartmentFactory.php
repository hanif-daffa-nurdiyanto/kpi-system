<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Department::class;

    public function definition(): array
    {
        $departments = [
            'Sales' => 'Handles outbound calls, auto quotes, and sales.',
            'Customer Service' => 'Handles customer inquiries and issue resolution.',
            'Marketing' => 'Manages advertising, promotions, and social media campaigns.',
            'Operations' => 'Ensures smooth internal processes and logistics.',
            'Finance' => 'Handles financial planning, budgets, and payroll.',
        ];

        $name = array_rand($departments);

        return [
            'name' => $name,
            'description' => $departments[$name],
            'manager_id' => User::whereHas('roles', fn($q) => $q->where('name', 'manager'))
                ->inRandomOrder()
                ->first()?->id ?? User::factory(),
        ];
    }
}
