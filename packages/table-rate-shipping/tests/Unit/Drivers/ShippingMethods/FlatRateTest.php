<?php

namespace Lunar\Shipping\Tests\Unit\Drivers\ShippingMethods;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\FlatRate;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Tests\TestCase;
use Lunar\Shipping\Tests\TestUtils;

/**
 * @group lunar.shipping.drivers
 */
class FlatRateTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_get_flat_rate_shipping()
    {
        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        TaxClass::factory()->create([
            'default' => true,
        ]);

        $shippingZone = ShippingZone::factory()->create([
            'type' => 'countries',
        ]);

        $shippingMethod = ShippingMethod::factory()->create([
            'shipping_zone_id' => $shippingZone->id,
            'driver' => 'flat-rate',
            'data' => [
                'minimum_spend' => [
                    "{$currency->code}" => 200,
                ],
            ],
        ]);

        $shippingMethod->prices()->createMany([
            [
                'price' => 600,
                'tier' => 1,
                'currency_id' => $currency->id,
            ],
        ]);

        $cart = $this->createCart($currency, 500);

        $driver = new FlatRate();

        $request = new ShippingOptionRequest(
            cart: $cart,
            shippingMethod: $shippingMethod
        );

        $shippingOption = $driver->resolve($request);

        $this->assertInstanceOf(ShippingOption::class, $shippingOption);

        $this->assertEquals(600, $shippingOption->price->value);
    }
}
