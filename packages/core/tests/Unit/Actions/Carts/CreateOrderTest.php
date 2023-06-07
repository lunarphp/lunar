<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\DataTypes\ShippingOption;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Tests\TestCase;

/**
 * @group lunar.actions
 * @group lunar.actions.carts
 */
class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    private $cart;

    /** @test  */
    public function can_create_order()
    {
        CustomerGroup::factory()->create([
            'default' => true,
        ]);

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

        $taxClass = TaxClass::factory()->create();

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
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

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
            'unit_quantity' => 1,
        ]);

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

        $cart->shippingAddress->shippingOption = $shippingOption;

        $order = $cart->createOrder();

        $breakdown = $cart->taxBreakdown->map(function ($tax) {
            return [
                'description' => $tax['description'],
                'identifier' => $tax['identifier'],
                'percentage' => $tax['amounts']->min('percentage'),
                'total' => $tax['total']->value,
            ];
        })->values();

        $datacheck = [
            'user_id' => $cart->user_id,
            'channel_id' => $cart->channel_id,
            'status' => config('lunar.orders.draft_status'),
            'customer_reference' => null,
            'sub_total' => $cart->subTotal->value,
            'total' => $cart->total->value,
            'discount_total' => $cart->discountTotal?->value,
            'shipping_total' => $cart->shippingTotal?->value ?: 0,
            'tax_breakdown' => json_encode($breakdown),
        ];

        $cart = $cart->refresh();

        $this->assertInstanceOf(Order::class, $cart->draftOrder);
        $this->assertEquals($cart->id, $order->cart_id);
        $this->assertCount(1, $cart->lines);
        $this->assertCount(2, $order->lines);
        $this->assertCount(2, $cart->addresses);
        $this->assertCount(2, $order->addresses);
        $this->assertInstanceOf(OrderAddress::class, $order->shippingAddress);
        $this->assertInstanceOf(OrderAddress::class, $order->billingAddress);

        $this->assertDatabaseHas((new Order())->getTable(), $datacheck);
        $this->assertDatabaseHas((new OrderLine())->getTable(), [
            'identifier' => $shippingOption->getIdentifier(),
        ]);

        $order->save();
        $containsCurrency = str_contains($order->fresh()->getRawOriginal('tax_breakdown'), '"currency"');
        $this->assertFalse($containsCurrency);
    }

//    /** @test */
//    public function cannot_create_order_without_billing_address()
//    {
//        $this->expectException(CartException::class);
//
//        $cart = Cart::factory()->create();
//
//        $cart->createOrder();
//
//        $this->assertNull($cart->refresh()->order_id);
//        $this->assertInstanceOf(Order::class, $cart->refresh()->order);
//    }
//
//    /** @test */
//    public function cannot_create_order_with_incomplete_billing_address()
//    {
//        $cart = Cart::factory()->create();
//
//        $cart->addresses()->create([
//            'type' => 'billing',
//            'postcode' => 'H0H 0H0',
//        ]);
//
//        $this->expectException(CartException::class);
//
//        $cart->createOrder();
//
//        $this->assertNull($cart->refresh()->order_id);
//        $this->assertInstanceOf(Order::class, $cart->refresh()->order);
//    }
//
//    /** @test */
//    public function can_set_tax_breakdown_correctly()
//    {
//        CustomerGroup::factory()->create([
//            'default' => true,
//        ]);
//
//        $billing = CartAddress::factory()->make([
//            'type' => 'billing',
//            'country_id' => Country::factory(),
//            'first_name' => 'Santa',
//            'line_one' => '123 Elf Road',
//            'city' => 'Lapland',
//            'postcode' => 'BILL',
//        ]);
//
//        $shipping = CartAddress::factory()->make([
//            'type' => 'shipping',
//            'country_id' => Country::factory(),
//            'first_name' => 'Santa',
//            'line_one' => '123 Elf Road',
//            'city' => 'Lapland',
//            'postcode' => 'SHIPP',
//        ]);
//
//        $currency = Currency::factory()->create([
//            'decimal_places' => 2,
//        ]);
//
//        $cart = Cart::factory()->create([
//            'currency_id' => $currency->id,
//        ]);
//
//        $taxClass = TaxClass::factory()->create([
//            'name' => 'Foobar',
//        ]);
//
//        $taxRate = TaxRate::factory()->create();
//
//        $taxRateAmount = TaxRateAmount::factory()->create([
//            'percentage' => 20,
//            'tax_class_id' => $taxClass->id,
//            'tax_rate_id' => $taxRate->id,
//        ]);
//
//        $purchasable = ProductVariant::factory()->create([
//            'tax_class_id' => $taxClass->id,
//            'unit_quantity' => 1,
//        ]);
//
//        Price::factory()->create([
//            'price' => 100,
//            'tier' => 1,
//            'currency_id' => $currency->id,
//            'priceable_type' => get_class($purchasable),
//            'priceable_id' => $purchasable->id,
//        ]);
//
//        $cart->lines()->create([
//            'purchasable_type' => get_class($purchasable),
//            'purchasable_id' => $purchasable->id,
//            'quantity' => 1,
//        ]);
//
//        $cart->addresses()->createMany([
//            $billing->toArray(),
//            $shipping->toArray(),
//        ]);
//
//        $shippingOption = new ShippingOption(
//            name: 'Basic Delivery',
//            description: 'Basic Delivery',
//            identifier: 'BASDEL',
//            price: new PriceDataType(500, $cart->currency, 1),
//            taxClass: $taxClass
//        );
//
//        ShippingManifest::addOption($shippingOption);
//
//        $cart->shippingAddress->update([
//            'shipping_option' => $shippingOption->getIdentifier(),
//        ]);
//
//        $order = $cart->createOrder();
//
//        $this->assertEquals(
//            $taxRateAmount->percentage,
//            $order->tax_breakdown->first()->percentage
//        );
//    }
//
//    /** @test  */
//    public function increments_discount_uses()
//    {
//        $customerGroup = CustomerGroup::factory()->create([
//            'default' => true,
//        ]);
//
//        $channel = Channel::factory()->create([
//            'default' => true,
//        ]);
//
//        $billing = CartAddress::factory()->make([
//            'type' => 'billing',
//            'country_id' => Country::factory(),
//            'first_name' => 'Santa',
//            'line_one' => '123 Elf Road',
//            'city' => 'Lapland',
//            'postcode' => 'BILL',
//        ]);
//
//        $shipping = CartAddress::factory()->make([
//            'type' => 'shipping',
//            'country_id' => Country::factory(),
//            'first_name' => 'Santa',
//            'line_one' => '123 Elf Road',
//            'city' => 'Lapland',
//            'postcode' => 'SHIPP',
//        ]);
//
//        $taxClass = TaxClass::factory()->create();
//
//        $currency = Currency::factory()->create([
//            'decimal_places' => 2,
//        ]);
//
//        $cart = Cart::factory()->create([
//            'currency_id' => $currency->id,
//            'channel_id' => $channel->id,
//            'coupon_code' => '10OFF',
//        ]);
//
//        $taxClass = TaxClass::factory()->create([
//            'name' => 'Foobar',
//        ]);
//
//        $taxClass->taxRateAmounts()->create(
//            TaxRateAmount::factory()->make([
//                'percentage' => 20,
//                'tax_class_id' => $taxClass->id,
//            ])->toArray()
//        );
//
//        $purchasable = ProductVariant::factory()->create([
//            'tax_class_id' => $taxClass->id,
//            'unit_quantity' => 1,
//        ]);
//
//        Price::factory()->create([
//            'price' => 1000,
//            'tier' => 1,
//            'currency_id' => $currency->id,
//            'priceable_type' => get_class($purchasable),
//            'priceable_id' => $purchasable->id,
//        ]);
//
//        $cart->lines()->create([
//            'purchasable_type' => get_class($purchasable),
//            'purchasable_id' => $purchasable->id,
//            'quantity' => 1,
//        ]);
//
//        $cart->addresses()->createMany([
//            $billing->toArray(),
//            $shipping->toArray(),
//        ]);
//
//        $shippingOption = new ShippingOption(
//            name: 'Basic Delivery',
//            description: 'Basic Delivery',
//            identifier: 'BASDEL',
//            price: new PriceDataType(500, $cart->currency, 1),
//            taxClass: $taxClass
//        );
//
//        ShippingManifest::addOption($shippingOption);
//
//        $cart->shippingAddress->update([
//            'shipping_option' => $shippingOption->getIdentifier(),
//        ]);
//
//        $cart->shippingAddress->shippingOption = $shippingOption;
//
//        $discount = Discount::factory()->create([
//            'type' => AmountOff::class,
//            'name' => 'Test Coupon',
//            'coupon' => '10OFF',
//            'data' => [
//                'fixed_value' => true,
//                'fixed_values' => [
//                    $currency->code => 1,
//                ],
//            ],
//        ]);
//
//        $discount->customerGroups()->sync([
//            $customerGroup->id => [
//                'enabled' => true,
//                'starts_at' => now(),
//            ],
//        ]);
//
//        $discount->channels()->sync([
//            $channel->id => [
//                'enabled' => true,
//                'starts_at' => now()->subHour(),
//            ],
//        ]);
//
//        $order = $cart->createOrder();
//
//        $cart = $cart->refresh();
//
//        $discount = $discount->refresh();
//
//        $this->assertInstanceOf(Order::class, $cart->order);
//        $this->assertEquals(1, $discount->uses);
//    }
//
//    /** @test  */
//    public function creates_a_discount_breakdown()
//    {
//        $customerGroup = CustomerGroup::factory()->create([
//            'default' => true,
//        ]);
//
//        $channel = Channel::factory()->create([
//            'default' => true,
//        ]);
//
//        $billing = CartAddress::factory()->make([
//            'type' => 'billing',
//            'country_id' => Country::factory(),
//            'first_name' => 'Santa',
//            'line_one' => '123 Elf Road',
//            'city' => 'Lapland',
//            'postcode' => 'BILL',
//        ]);
//
//        $shipping = CartAddress::factory()->make([
//            'type' => 'shipping',
//            'country_id' => Country::factory(),
//            'first_name' => 'Santa',
//            'line_one' => '123 Elf Road',
//            'city' => 'Lapland',
//            'postcode' => 'SHIPP',
//        ]);
//
//        $taxClass = TaxClass::factory()->create();
//
//        $currency = Currency::factory()->create([
//            'decimal_places' => 2,
//        ]);
//
//        $cart = Cart::factory()->create([
//            'currency_id' => $currency->id,
//            'channel_id' => $channel->id,
//            'coupon_code' => '10OFF',
//        ]);
//
//        $taxClass = TaxClass::factory()->create([
//            'name' => 'Foobar',
//        ]);
//
//        $taxClass->taxRateAmounts()->create(
//            TaxRateAmount::factory()->make([
//                'percentage' => 20,
//                'tax_class_id' => $taxClass->id,
//            ])->toArray()
//        );
//
//        $purchasable = ProductVariant::factory()->create([
//            'tax_class_id' => $taxClass->id,
//            'unit_quantity' => 1,
//        ]);
//
//        Price::factory()->create([
//            'price' => 1000,
//            'tier' => 1,
//            'currency_id' => $currency->id,
//            'priceable_type' => get_class($purchasable),
//            'priceable_id' => $purchasable->id,
//        ]);
//
//        $cart->lines()->create([
//            'purchasable_type' => get_class($purchasable),
//            'purchasable_id' => $purchasable->id,
//            'quantity' => 1,
//        ]);
//
//        $cart->addresses()->createMany([
//            $billing->toArray(),
//            $shipping->toArray(),
//        ]);
//
//        $shippingOption = new ShippingOption(
//            name: 'Basic Delivery',
//            description: 'Basic Delivery',
//            identifier: 'BASDEL',
//            price: new PriceDataType(500, $cart->currency, 1),
//            taxClass: $taxClass
//        );
//
//        ShippingManifest::addOption($shippingOption);
//
//        $cart->shippingAddress->update([
//            'shipping_option' => $shippingOption->getIdentifier(),
//        ]);
//
//        $cart->shippingAddress->shippingOption = $shippingOption;
//
//        $discount = Discount::factory()->create([
//            'type' => AmountOff::class,
//            'name' => 'Test Coupon',
//            'coupon' => '10OFF',
//            'data' => [
//                'fixed_value' => true,
//                'fixed_values' => [
//                    $currency->code => 1,
//                ],
//            ],
//        ]);
//
//        $discount->customerGroups()->sync([
//            $customerGroup->id => [
//                'enabled' => true,
//                'starts_at' => now(),
//            ],
//        ]);
//
//        $discount->channels()->sync([
//            $channel->id => [
//                'enabled' => true,
//                'starts_at' => now()->subHour(),
//            ],
//        ]);
//
//        $order = $cart->createOrder();
//
//        $this->assertCount(1, $order->discount_breakdown);
//        $this->assertEquals($purchasable->id, $order->discount_breakdown->first()->lines->first()->line->purchasable->id);
//        $this->assertEquals(100, $order->discount_breakdown->first()->total->value);
//    }
}
