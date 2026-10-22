<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use LogicException;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Sample records may only be created locally or in tests.');
        }

        Attendance::factory()->create();
    }
}
