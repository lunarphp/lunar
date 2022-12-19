<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\DiscountTypes\Coupon;
use Lunar\Models\Discount;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->name;

        return [
            'name' => $name,
            'handle' => Str::snake($name),
            'type' => Coupon::class,
            'starts_at' => now(),
        ];
    }
}
