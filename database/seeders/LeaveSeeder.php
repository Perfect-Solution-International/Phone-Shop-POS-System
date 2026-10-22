<?php

namespace Database\Seeders;

use App\Models\Leave;
use Illuminate\Database\Seeder;
use LogicException;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Sample records may only be created locally or in tests.');
        }

        Leave::factory()->create();
    }
}
