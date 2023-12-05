<?php

namespace Lunar\Shipping\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Shipping\Models\ShippingMethod;

class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence,
            'driver' => 'ship-by',
            'code' => $this->faker->word,
            'enabled' => true,
            'data' => [],
        ];
    }
}
