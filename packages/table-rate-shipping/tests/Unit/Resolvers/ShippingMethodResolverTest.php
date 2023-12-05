<?php

namespace Lunar\Shipping\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\State;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Tests\TestCase;
use Lunar\Shipping\Tests\TestUtils;

/**
 * @group lunar.shipping-methods
 */
class ShippingMethodResolverTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_fetch_shipping_methods_by_country()
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

        $this->assertCount(1, $shippingMethods);
        $this->assertEquals($shippingMethod->id, $shippingMethods->first()->id);

        $cart = $this->createCart($currency, 500);

        $secondCountry = Country::factory()->create();

        $cart->shippingAddress()->create(
            CartAddress::factory()->make([
                'country_id' => $secondCountry->id,
                'state' => null,
            ])->toArray()
        );

        $shippingMethods = Shipping::shippingMethods(
            $cart->refresh()->calculate()
        )->get();

        $this->assertEmpty($shippingMethods);
    }

    /** @test */
    public function can_fetch_shipping_methods_by_state()
    {
        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        $country = Country::factory()->create();

        TaxClass::factory()->create([
            'default' => true,
        ]);

        $shippingZone = ShippingZone::factory()->create([
            'type' => 'states',
        ]);

        $state = State::factory()->create([
            'country_id' => $country->id,
        ]);

        $shippingZone->states()->attach($state);

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
                'state' => $state->name,
            ])->toArray()
        );

        $shippingMethods = Shipping::shippingMethods(
            $cart->refresh()->calculate()
        )->get();

        $this->assertCount(1, $shippingMethods);
        $this->assertEquals($shippingMethod->id, $shippingMethods->first()->id);
    }

    /** @test */
    public function can_fetch_shipping_methods_by_postcode()
    {
        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        $country = Country::factory()->create();

        TaxClass::factory()->create([
            'default' => true,
        ]);

        $shippingZone = ShippingZone::factory()->create([
            'type' => 'postcodes',
        ]);

        $shippingZone->postcodes()->create([
            'postcode' => 'AB1',
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
                'postcode' => 'AB1 1CD',
            ])->toArray()
        );

        $shippingMethods = Shipping::shippingMethods(
            $cart->refresh()->calculate()
        )->get();

        $this->assertCount(1, $shippingMethods);
        $this->assertEquals($shippingMethod->id, $shippingMethods->first()->id);
    }
    
    /** @test */
    public function can_reject_shipping_methods_when_stock_is_not_available()
    {
        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        $country = Country::factory()->create();

        TaxClass::factory()->create([
            'default' => true,
        ]);

        $shippingZone = ShippingZone::factory()->create([
            'type' => 'postcodes',
        ]);

        $shippingZone->postcodes()->create([
            'postcode' => 'AB1',
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
            'stock_available' => 1
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
        
        $purchasable = ProductVariant::factory()->create();
        $purchasable->stock = 0;

        Price::factory()->create([
            'price' => 200,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        $cart->shippingAddress()->create(
            CartAddress::factory()->make([
                'country_id' => $country->id,
                'state' => null,
                'postcode' => 'AB1 1CD',
            ])->toArray()
        );

        $shippingMethods = Shipping::shippingMethods(
            $cart->refresh()->calculate()
        )->get();

        $this->assertCount(0, $shippingMethods);
    }
}
