<?php

namespace Lunar\Shipping\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Models\ShippingZonePostcode;
use Lunar\Shipping\Tests\TestCase;

/**
 * @group hub.shipping.models
 */
class ShippingZonePostcodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_model()
    {
        $shippingZone = ShippingZone::factory()->create();

        ShippingZonePostcode::create([
            'shipping_zone_id' => $shippingZone->id,
            'postcode' => 'AB1 2BA',
        ]);

        $this->assertDatabaseHas((new ShippingZonePostcode())->getTable(), [
            'shipping_zone_id' => $shippingZone->id,
            'postcode' => 'AB12BA',
        ]);
    }

    /** @test */
    public function can_fetch_shipping_zone_relationship()
    {
        $shippingZone = ShippingZone::factory()->create();

        $postcode = ShippingZonePostcode::create([
            'shipping_zone_id' => $shippingZone->id,
            'postcode' => 'AB1 2BA',
        ]);

        $this->assertInstanceOf(ShippingZone::class, $postcode->shippingZone);
        $this->assertEquals($shippingZone->id, $postcode->shippingZone->id);
    }

    /** @test */
    public function postcode_is_sanitised_on_save()
    {
        $shippingZone = ShippingZone::factory()->create();

        $postcode = ShippingZonePostcode::create([
            'shipping_zone_id' => $shippingZone->id,
            'postcode' => 'AB1 2BA',
        ]);

        $this->assertEquals('AB12BA', $postcode->postcode);

        $postcodeTests = [
            '          A B 1 2     B A',
            'AB 12 BA',
            'AB1       2BA ',
        ];

        foreach ($postcodeTests as $ptest) {
            $postcode->update([
                'postcode' => $ptest,
            ]);
            $this->assertEquals('AB12BA', $postcode->refresh()->postcode);
        }
    }
}
