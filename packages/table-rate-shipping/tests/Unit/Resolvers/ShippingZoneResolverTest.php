<?php

namespace Lunar\Shipping\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Shipping\DataTransferObjects\PostcodeLookup;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Resolvers\ShippingZoneResolver;
use Lunar\Shipping\Tests\TestCase;

/**
 * @group lunar.shipping
 */
class ShippingZoneResolverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_fetch_shipping_zones_by_country()
    {
        $countryA = Country::factory()->create();
        $countryB = Country::factory()->create();

        $shippingZoneA = ShippingZone::factory()->create([
            'type' => 'countries',
        ]);

        $shippingZoneB = ShippingZone::factory()->create([
            'type' => 'countries',
        ]);

        $shippingZoneA->countries()->attach($countryA);
        $shippingZoneB->countries()->attach($countryB);

        $this->assertCount(1, $shippingZoneA->refresh()->countries);

        $zones = (new ShippingZoneResolver())->country($countryA)->get();

        $this->assertCount(1, $zones);

        $this->assertEquals($shippingZoneA->id, $zones->first()->id);
    }

    /** @test */
    public function can_fetch_shipping_zones_by_state()
    {
        $countryA = Country::factory()->create();
        $countryB = Country::factory()->create();

        $stateA = State::factory()->create([
            'country_id' => $countryA->id,
        ]);

        $stateB = State::factory()->create([
            'country_id' => $countryB->id,
        ]);

        $shippingZoneA = ShippingZone::factory()->create([
            'type' => 'states',
        ]);

        $shippingZoneB = ShippingZone::factory()->create([
            'type' => 'countries',
        ]);

        $shippingZoneA->states()->attach($stateA);
        $shippingZoneB->states()->attach($stateB);

        $this->assertCount(1, $shippingZoneA->refresh()->states);

        $zones = (new ShippingZoneResolver())->state($stateA)->get();

        $this->assertCount(1, $zones);

        $this->assertEquals($shippingZoneA->id, $zones->first()->id);
    }

    /** @test */
    public function doesnt_fetch_postcode_shipping_zones_by_country()
    {
        $countryA = Country::factory()->create();

        $shippingZoneA = ShippingZone::factory()->create([
            'type' => 'postcodes',
        ]);

        $shippingZoneA->countries()->attach($countryA);

        $this->assertCount(1, $shippingZoneA->refresh()->countries);

        $zones = (new ShippingZoneResolver())->country($countryA)->get();

        $this->assertEmpty($zones);
    }

    /** @test */
    public function can_fetch_zone_by_postcode_lookup()
    {
        $country = Country::factory()->create();

        $shippingZone = ShippingZone::factory()->create([
            'type' => 'postcodes',
        ]);

        $shippingZone->countries()->attach($country);

        $shippingZone->postcodes()->create([
            'postcode' => 'ABC',
        ]);

        $this->assertCount(1, $shippingZone->refresh()->countries);
        $this->assertCount(1, $shippingZone->refresh()->postcodes);

        $postcode = new PostcodeLookup(
            $country,
            'ABC 123'
        );

        $zones = (new ShippingZoneResolver())->postcode($postcode)->get();

        $this->assertCount(1, $zones);

        $this->assertEquals($shippingZone->id, $zones->first()->id);
    }
}
