<?php

namespace Lunar\Shipping\Tests\Unit\Drivers\ShippingMethods;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\FreeShipping;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Tests\TestCase;
use Lunar\Shipping\Tests\TestUtils;

/**
 * @group lunar.shipping.drivers
 */
class FreeShippingTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_get_free_shipping()
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
            'driver' => 'free-shipping',
            'data' => [
                'minimum_spend' => [
                    "{$currency->code}" => 500,
                ],
            ],
        ]);

        $cart = $this->createCart($currency, 500);

        $driver = new FreeShipping();

        $request = new ShippingOptionRequest(
            cart: $cart,
            shippingMethod: $shippingMethod
        );

        $shippingOption = $driver->resolve($request);

        $this->assertInstanceOf(ShippingOption::class, $shippingOption);
    }

    /** @test */
    public function cant_get_free_shipping_if_minimum_isnt_met()
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
            'driver' => 'free-shipping',
            'data' => [
                'minimum_spend' => [
                    "{$currency->code}" => 500,
                ],
            ],
        ]);

        $cart = $this->createCart($currency, 50);

        $driver = new FreeShipping();

        $request = new ShippingOptionRequest(
            cart: $cart,
            shippingMethod: $shippingMethod
        );

        $shippingOption = $driver->resolve($request);

        $this->assertNull($shippingOption);
    }

    /** @test */
    public function cant_get_free_shipping_if_currency_isnt_met()
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
            'driver' => 'free-shipping',
            'data' => [
                'minimum_spend' => [
                    'FOO' => 500,
                ],
            ],
        ]);

        $cart = $this->createCart($currency, 10000);

        $driver = new FreeShipping();

        $request = new ShippingOptionRequest(
            cart: $cart,
            shippingMethod: $shippingMethod
        );

        $shippingOption = $driver->resolve($request);

        $this->assertNull($shippingOption);
    }
}
