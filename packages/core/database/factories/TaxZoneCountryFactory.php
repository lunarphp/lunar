<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Country;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

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
