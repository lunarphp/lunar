<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Country;
use GetCandy\Models\TaxZone;
use GetCandy\Models\TaxZoneCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxZoneCountryFactory extends Factory
{
    protected $model = TaxZoneCountry::class;

    public function definition(): array
    {
        return [
            'tax_zone_id' => TaxZone::factory(),
            'country_id' => Country::factory(),
        ];
    }
}
