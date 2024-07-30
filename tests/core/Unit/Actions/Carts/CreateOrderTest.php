<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Actions\Carts\CreateOrder;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\DataTypes\ShippingOption;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/** @test  */
function cant_create_order_if_already_has_complete_and_multiple_disabled()
{
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => now(),
    ]);

    $this->expectException(DisallowMultipleCartOrdersException::class);

    (new CreateOrder)->execute($cart);
}

test('can create order if multiple enabled', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => now(),
    ]);

    $newOrder = (new CreateOrder)->execute($cart, allowMultipleOrders: true)->then(
        fn ($order) => $order->refresh()
    );

    $this->assertNotSame($newOrder->id, $order->id);
});

/** @test  */
function can_update_draft_order()
{
    TaxClass::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $updatedAt = now()->setTime('10', '00', '00');

    $orderA = Order::factory()->create([
        'cart_id' => $cart->id,
        'updated_at' => $updatedAt,
        'placed_at' => now(),
    ]);

    $orderB = Order::factory()->create([
        'cart_id' => $cart->id,
        'updated_at' => $updatedAt,
    ]);

    $updatedOrder = (new CreateOrder)->execute($cart, allowMultipleOrders: true)->then(
        fn ($order) => $order->refresh()
    );

    expect($orderB->id)->toBe($updatedOrder->id);
    expect($orderB->updated_at->eq($updatedOrder->updated_at))->toBeFalse();
    expect($orderA->updated_at->eq($updatedAt))->toBeTrue();
}

test('can create order', function () {
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
        'min_quantity' => 1,
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

    $breakdown = $cart->taxBreakdown->amounts->mapWithKeys(function ($tax, $key) {
        return [$key => [
            'description' => $tax->description,
            'identifier' => $tax->identifier,
            'percentage' => $tax->percentage,
            'value' => $tax->price->value,
            'currency_code' => $tax->price->currency->code,
        ]];
    });

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

    $cart = $cart->refresh()->calculate();

    expect($cart->currentDraftOrder())->toBeInstanceOf(Order::class)
        ->and($order->cart_id)->toEqual($cart->id)
        ->and($cart->lines)->toHaveCount(1)
        ->and($order->lines)->toHaveCount(2)
        ->and($cart->addresses)->toHaveCount(2)
        ->and($order->addresses)->toHaveCount(2)
        ->and($order->shippingAddress)->toBeInstanceOf(OrderAddress::class)
        ->and($order->billingAddress)->toBeInstanceOf(OrderAddress::class);

    $this->assertDatabaseHas((new Order)->getTable(), $datacheck);
    $this->assertDatabaseHas((new OrderLine)->getTable(), [
        'identifier' => $shippingOption->getIdentifier(),
    ]);

    $order->save();
    $containsCurrency = str_contains($order->fresh()->getRawOriginal('tax_breakdown'), '"currency"');
    expect($containsCurrency)->toBeFalse();
});

test('can create order with customer', function () {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $customer = Customer::factory()->create();

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
        'customer_id' => $customer->id,
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
        'min_quantity' => 1,
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

    $breakdown = $cart->taxBreakdown->amounts->mapWithKeys(function ($tax, $key) {
        return [$key => [
            'description' => $tax->description,
            'identifier' => $tax->identifier,
            'percentage' => $tax->percentage,
            'value' => $tax->price->value,
            'currency_code' => $tax->price->currency->code,
        ]];
    });

    $datacheck = [
        'user_id' => $cart->user_id,
        'customer_id' => $cart->customer_id,
        'channel_id' => $cart->channel_id,
        'status' => config('lunar.orders.draft_status'),
        'customer_reference' => null,
        'sub_total' => $cart->subTotal->value,
        'total' => $cart->total->value,
        'discount_total' => $cart->discountTotal?->value,
        'shipping_total' => $cart->shippingTotal?->value ?: 0,
        'tax_breakdown' => json_encode($breakdown),
    ];

    $cart = $cart->refresh()->calculate();

    $this->assertDatabaseHas((new Order)->getTable(), $datacheck);
});
