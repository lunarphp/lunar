<?php

namespace Lunar\Database\Factories;

use Lunar\Models\TaxRate;
use Lunar\Models\TaxZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'tax_zone_id' => TaxZone::factory(),
            'name'        => $this->faker->name,
            'priority'    => $this->faker->numberBetween(1, 50),
        ];
    }
}
