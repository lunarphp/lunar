<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Country;
use GetCandy\Models\TaxZone;
use GetCandy\Models\TaxZonePostcode;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.models
 */
class TaxZonePostcodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_tax_zone_postcode()
    {
        $data = [
            'tax_zone_id' => TaxZone::factory()->create()->id,
            'country_id' => Country::factory()->create()->id,
            'postcode' => 123456,
        ];

        TaxZonePostcode::factory()->create($data);

        $this->assertDatabaseHas((new TaxZonePostcode)->getTable(), $data);
    }
}
