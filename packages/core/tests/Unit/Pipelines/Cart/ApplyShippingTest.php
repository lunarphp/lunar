<?php

namespace Lunar\Tests\Unit\Pipelines\Cart;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;
use Lunar\Pipelines\Cart\ApplyShipping;
use Lunar\Tests\TestCase;

/**
 * @group lunar.carts.pipelines
 */
class ApplyShippingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_apply_empty_shipping_totals()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 100,
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

        $this->assertNull($cart->shippingTotal);

        app(ApplyShipping::class)->handle($cart, function ($cart) {
            return $cart;
        });

        $this->assertInstanceOf(PriceDataType::class, $cart->shippingSubTotal);
    }

    /** @test */
    public function can_apply_shipping_totals()
    {
        $currency = Currency::factory()->create();

        $billing = CartAddress::factory()->make([
            'type' => 'billing',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'BILL',
        ]);

        $shipping = CartAddress::factory()->make([
            'type' => 'shipping',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'SHIPP',
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $taxClass = TaxClass::factory()->create([
            'name' => 'Foobar',
        ]);

        $taxClass->taxRateAmounts()->create(
            TaxRateAmount::factory()->make([
                'percentage' => 20,
                'tax_class_id' => $taxClass->id,
            ])->toArray()
        );

        $cart->addresses()->createMany([
            $billing->toArray(),
            $shipping->toArray(),
        ]);

        $shippingOption = new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new PriceDataType(500, $cart->currency, 1),
            taxClass: $taxClass
        );

        ShippingManifest::addOption($shippingOption);

        $cart->shippingAddress->update([
            'shipping_option' => $shippingOption->getIdentifier(),
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 100,
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

        $this->assertNull($cart->shippingTotal);

        app(ApplyShipping::class)->handle($cart, function ($cart) {
            return $cart;
        });

        $this->assertInstanceOf(PriceDataType::class, $cart->shippingSubTotal);
        $this->assertEquals(500, $cart->shippingSubTotal->value);
    }
}
