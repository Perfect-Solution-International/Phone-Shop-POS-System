<?php

namespace Database\Seeders;

use App\Models\Payroll;
use Illuminate\Database\Seeder;
use LogicException;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Sample records may only be created locally or in tests.');
        }

        Payroll::factory()->create();
    }
}
