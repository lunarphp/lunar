<?php

namespace Lunar\Tests\Unit\Actions\Taxes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Taxes\GetTaxZoneCountry;
use Lunar\Models\Country;
use Lunar\Models\TaxZoneCountry;
use Lunar\Tests\TestCase;

/**
 * @group lunar.actions
 */
class GetTaxZoneCountryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_match_country_id()
    {
        $belgium = Country::factory()->create([
            'name' => 'Belgium',
        ]);

        $uk = Country::factory()->create([
            'name' => 'United Kingdom',
        ]);

        $taxZoneBelgium = TaxZoneCountry::factory()->create([
            'country_id' => $belgium->id,
        ]);

        $taxZoneUk = TaxZoneCountry::factory()->create([
            'country_id' => $uk->id,
        ]);

        $zone = app(GetTaxZoneCountry::class)->execute($uk->id);

        $this->assertEquals($taxZoneUk->id, $zone->id);
    }

    /** @test */
    public function can_mismatch_country_id()
    {
        $belgium = Country::factory()->create([
            'name' => 'Belgium',
        ]);

        $uk = Country::factory()->create([
            'name' => 'United Kingdom',
        ]);

        $taxZoneBelgium = TaxZoneCountry::factory()->create([
            'country_id' => $belgium->id,
        ]);

        $zone = app(GetTaxZoneCountry::class)->execute($uk->id);

        $this->assertNull($zone);
    }
}
