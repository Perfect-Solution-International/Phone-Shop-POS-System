<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use LogicException;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Sample records may only be created locally or in tests.');
        }

        Employee::factory()->create();
    }
}
