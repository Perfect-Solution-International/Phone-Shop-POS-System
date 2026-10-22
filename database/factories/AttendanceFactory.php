<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Attendance> */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'shop_id' => fn (array $attributes) => Employee::withoutGlobalScope('shop')->findOrFail($attributes['employee_id'])->shop_id,
            'work_date' => '2026-10-21',
            'clocked_in_at' => '2026-10-21 03:30:00',
            'clocked_out_at' => null,
        ];
    }
}
