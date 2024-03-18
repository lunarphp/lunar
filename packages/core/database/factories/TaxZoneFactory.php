<?php

namespace Lunar\Database\Factories;

use Lunar\Models\TaxZone;

class TaxZoneFactory extends BaseFactory
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
