<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Country;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Models\TaxZoneCountry;

class TaxZoneCountryFactory extends BaseFactory
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
