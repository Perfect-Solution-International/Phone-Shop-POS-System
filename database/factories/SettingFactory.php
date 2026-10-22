<?php

namespace Database\Factories;

use App\Models\Platform\Shop;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Setting> */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        return ['shop_id' => Shop::factory(), 'timezone' => 'Asia/Colombo', 'currency' => 'LKR'];
    }
}
