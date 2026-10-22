<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payroll> */
class PayrollFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'shop_id' => fn (array $attributes) => Employee::withoutGlobalScope('shop')->findOrFail($attributes['employee_id'])->shop_id,
            'period' => '2026-10-01',
            'basic_salary' => '50000.00',
            'allowances' => '5000.00',
            'deductions' => '1000.00',
            'status' => 'draft',
        ];
    }
}
