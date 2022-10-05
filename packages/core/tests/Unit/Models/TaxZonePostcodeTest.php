<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Country;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZonePostcode;
use Lunar\Tests\TestCase;

/**
 * @group lunar.models
 */
class TaxZonePostcodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_tax_zone_postcode()
    {
        $data = [
            'tax_zone_id' => TaxZone::factory()->create()->id,
            'country_id'  => Country::factory()->create()->id,
            'postcode'    => 123456,
        ];

        TaxZonePostcode::factory()->create($data);

        $this->assertDatabaseHas((new TaxZonePostcode())->getTable(), $data);
    }
}
