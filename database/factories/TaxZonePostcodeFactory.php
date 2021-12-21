<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Country;
use GetCandy\Models\TaxZone;
use GetCandy\Models\TaxZonePostcode;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxZonePostcodeFactory extends Factory
{
    protected $model = TaxZonePostcode::class;

    public function definition(): array
    {
        return [
            'tax_zone_id' => TaxZone::factory(),
            'country_id' => Country::factory(),
            'postcode' => $this->faker->postcode,
        ];
    }
}
