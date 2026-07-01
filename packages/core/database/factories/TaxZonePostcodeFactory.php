<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Models\TaxZonePostcode;

class TaxZonePostcodeFactory extends BaseFactory
{
    protected $model = TaxZonePostcode::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'tax_zone_id' => TaxZone::factory(),
            'country_id' => Country::factory(),
            'postcode' => $this->faker->postcode,
        ];
    }
}
