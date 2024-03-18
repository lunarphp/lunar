<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Currency;

class CurrencyFactory extends BaseFactory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'code' => $this->faker->unique()->currencyCode,
            'enabled' => true,
            'exchange_rate' => $this->faker->randomFloat(2, 0.1, 5),
            'decimal_places' => 2,
            'default' => true,
        ];
    }
}
