<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\Country;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;

class TaxZoneCountryFactory extends Factory
{
    protected $model = TaxZoneCountry::class;

    public function definition(): array
    {
        return [
            'tax_zone_id' => TaxZone::factory(),
            'country_id'  => Country::factory(),
        ];
    }
}
