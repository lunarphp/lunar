<?php

namespace Lunar\Shipping\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionLookup;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Tests\TestCase;
use Lunar\Shipping\Tests\TestUtils;

/**
 * @group lunar.shipping-methods
 */
class ShippingOptionResolverTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_fetch_shipping_options()
    {
        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        $country = Country::factory()->create();

        TaxClass::factory()->create([
            'default' => true,
        ]);

        $shippingZone = ShippingZone::factory()->create([
            'type' => 'countries',
        ]);

        $shippingZone->countries()->attach($country);

        $shippingMethod = ShippingMethod::factory()->create([
            'shipping_zone_id' => $shippingZone->id,
            'driver' => 'ship-by',
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
            [
                'price' => 500,
                'tier' => 700,
                'currency_id' => $currency->id,
            ],
            [
                'price' => 0,
                'tier' => 800,
                'currency_id' => $currency->id,
            ],
        ]);

        $cart = $this->createCart($currency, 500);

        $cart->shippingAddress()->create(
            CartAddress::factory()->make([
                'country_id' => $country->id,
                'state' => null,
            ])->toArray()
        );

        $shippingMethods = Shipping::shippingMethods(
            $cart->refresh()->calculate()
        )->get();

        $options = Shipping::shippingOptions()->cart(
            $cart->refresh()->calculate()
        )->get(
            new ShippingOptionLookup(
                shippingMethods: $shippingMethods
            )
        );

        $this->assertcount(1, $options);
    }
}
