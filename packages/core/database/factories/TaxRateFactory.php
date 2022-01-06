<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\TaxRate;
use GetCandy\Models\TaxZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'tax_zone_id' => TaxZone::factory(),
            'name' => $this->faker->name,
            'priority' => $this->faker->numberBetween(1, 50),
        ];
    }
}
