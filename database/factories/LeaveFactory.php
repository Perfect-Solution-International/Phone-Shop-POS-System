<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Leave> */
class LeaveFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'shop_id' => fn (array $attributes) => Employee::withoutGlobalScope('shop')->findOrFail($attributes['employee_id'])->shop_id,
            'starts_on' => '2026-10-22',
            'ends_on' => '2026-10-23',
            'type' => 'annual',
            'status' => 'pending',
        ];
    }
}
