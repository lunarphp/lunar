<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Country;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZonePostcode;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxZonePostcodeFactory extends Factory
{
    protected $model = TaxZonePostcode::class;

    public function definition(): array
    {
        return [
            'tax_zone_id' => TaxZone::factory(),
            'country_id'  => Country::factory(),
            'postcode'    => $this->faker->postcode,
        ];
    }
}
