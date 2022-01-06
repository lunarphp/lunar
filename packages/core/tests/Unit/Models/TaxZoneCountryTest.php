<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Country;
use GetCandy\Models\TaxZone;
use GetCandy\Models\TaxZoneCountry;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.models
 */
class TaxZoneCountryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_tax_zone_country()
    {
        $data = [
            'tax_zone_id' => TaxZone::factory()->create()->id,
            'country_id' => Country::factory()->create()->id,
        ];

        TaxZoneCountry::factory()->create($data);

        $this->assertDatabaseHas((new TaxZoneCountry)->getTable(), $data);
    }
}
