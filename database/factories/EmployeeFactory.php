<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Platform\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employee> */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'employee_code' => fake()->unique()->bothify('EMP-#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'department' => 'Operations',
            'position' => 'Assistant',
            'joined_on' => fake()->date(),
            'is_active' => true,
        ];
    }
}
