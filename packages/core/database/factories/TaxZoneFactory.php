<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\TaxZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxZoneFactory extends Factory
{
    protected $model = TaxZone::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'zone_type' => $this->faker->randomElement(['country', 'postcode', 'state']),
            'price_display' => $this->faker->randomElement(['tax_inclusive', 'tax_exclusive']),
            'active' => $this->faker->boolean,
            'default' => true,
        ];
    }
}
