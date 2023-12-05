<?php

namespace Lunar\Shipping\Tests\Unit\Drivers\ShippingMethods;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\ShipBy;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Tests\TestCase;
use Lunar\Shipping\Tests\TestUtils;

/**
 * @group lunar.shipping.drivers
 */
class ShipByTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_get_shipping_option_by_cart_total()
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
            'driver' => 'ship-by',
            'data' => [
                'charge_by' => 'cart_total',
            ],
        ]);

        $shippingMethod->prices()->createMany([
            [
                'price' => 1000,
                'tier' => 1,
                'currency_id' => $currency->id,
            ],
            [
                'price' => 500,
                'tier' => 700,
                'currency_id' => $currency->id,
            ],
        ]);

        $this->assertCount(2, $shippingMethod->prices);

        $cart = $this->createCart($currency, 100);

        $driver = new ShipBy();

        $request = new ShippingOptionRequest(
            cart: $cart,
            shippingMethod: $shippingMethod
        );

        $shippingOption = $driver->resolve($request);

        $this->assertInstanceOf(ShippingOption::class, $shippingOption);

        $this->assertEquals(1000, $shippingOption->price->value);

        $cart = $this->createCart($currency, 10000);

        $driver = new ShipBy();

        $request = new ShippingOptionRequest(
            cart: $cart,
            shippingMethod: $shippingMethod
        );

        $shippingOption = $driver->resolve($request);

        $this->assertInstanceOf(ShippingOption::class, $shippingOption);

        $this->assertEquals(500, $shippingOption->price->value);
    }

    /* @test */
    public function can_get_shipping_option_if_outside_tier_without_default_price()
    {
        // Boom.
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
            'driver' => 'ship-by',
            'data' => [
                'charge_by' => 'cart_total',
            ],
        ]);

        $shippingMethod->prices()->createMany([
            [
                'price' => 500,
                'tier' => 700,
                'currency_id' => $currency->id,
            ],
        ]);

        $this->assertCount(1, $shippingMethod->prices);

        $cart = $this->createCart($currency, 100);

        $driver = new ShipBy();

        $request = new ShippingOptionRequest(
            cart: $cart,
            shippingMethod: $shippingMethod
        );

        $this->expectException(\ErrorException::class);

        $driver->resolve($request);
    }
}
