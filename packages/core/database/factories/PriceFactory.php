<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;

class PriceFactory extends BaseFactory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'price' => $this->faker->numberBetween(1, 2500),
            'list_price' => $this->faker->numberBetween(1, 2500),
            'currency_id' => Currency::factory(),
        ];
    }
}
