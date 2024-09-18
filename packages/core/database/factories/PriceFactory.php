<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Currency;
use Lunar\Models\Price;

class PriceFactory extends BaseFactory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'price' => $this->faker->numberBetween(1, 2500),
            'compare_price' => $this->faker->numberBetween(1, 2500),
            'currency_id' => Currency::factory(),
        ];
    }
}
