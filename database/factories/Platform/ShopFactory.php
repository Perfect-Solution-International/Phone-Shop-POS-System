<?php

namespace Database\Factories\Platform;

use App\Models\Platform\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Shop> */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        return ['name' => fake()->company()];
    }
}
