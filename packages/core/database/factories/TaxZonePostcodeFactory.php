<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Country;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZonePostcode;

class TaxZonePostcodeFactory extends BaseFactory
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
