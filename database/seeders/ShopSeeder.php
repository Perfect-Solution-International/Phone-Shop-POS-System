<?php

namespace Database\Seeders;

use App\Models\Platform\Shop;
use Illuminate\Database\Seeder;
use LogicException;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Sample shops may only be created locally or in tests.');
        }

        Shop::factory()->create();
    }
}
