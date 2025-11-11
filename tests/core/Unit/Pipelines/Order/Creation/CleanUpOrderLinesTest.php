<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Pipelines\Order\Creation\CleanUpOrderLines;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can run pipeline', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $cart->currency, 1),
            taxClass: TaxClass::factory()->create()
        )
    );

    CartAddress::factory()->create([
        'type' => 'shipping',
        'shipping_option' => 'BASDEL',
        'cart_id' => $cart->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    $purchasable = ProductVariant::factory()->create();
    $purchasableB = ProductVariant::factory()->create();

    \Lunar\Models\Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    \Lunar\Models\Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'purchasable_id' => $purchasable->id,
        'purchasable_type' => $purchasable->getMorphClass(),
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'purchasable_id' => $purchasableB->id,
        'purchasable_type' => $purchasableB->getMorphClass(),
    ]);

    OrderLine::factory()->create([
        'identifier' => 'BASDEL',
        'purchasable_type' => ShippingOption::class,
        'type' => 'shipping',
        'order_id' => $order->id,
    ]);

    $order = app(CleanUpOrderLines::class)->handle($order, function ($order) {
        return $order;
    });

    assertDatabaseHas((new OrderLine)->getTable(), [
        'order_id' => $order->id,
        'purchasable_id' => $purchasable->id,
    ]);

    assertDatabaseMissing((new OrderLine)->getTable(), [
        'order_id' => $order->id,
        'purchasable_id' => $purchasableB->id,
    ]);

    expect($order->shippingLines->first()->identifier)->toEqual('BASDEL');
});

test('will remove lines with same purchasable ids when different', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $cart->currency, 1),
            taxClass: TaxClass::factory()->create()
        )
    );

    CartAddress::factory()->create([
        'type' => 'shipping',
        'shipping_option' => 'BASDEL',
        'cart_id' => $cart->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    $purchasable = ProductVariant::factory()->create();
    $purchasableB = ProductVariant::factory()->create();

    \Lunar\Models\Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    \Lunar\Models\Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 5,
        'meta' => ['foo' => 'bar'],
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'purchasable_id' => $purchasable->id,
        'purchasable_type' => $purchasable->getMorphClass(),
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'quantity' => 15,
        'purchasable_id' => $purchasableB->id,
        'purchasable_type' => $purchasableB->getMorphClass(),
        'meta' => ['bar' => 'baz'],
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'quantity' => 5,
        'purchasable_id' => $purchasableB->id,
        'purchasable_type' => $purchasableB->getMorphClass(),
        'meta' => ['foo' => 'bar'],
    ]);

    OrderLine::factory()->create([
        'identifier' => 'BASDEL',
        'purchasable_type' => ShippingOption::class,
        'type' => 'shipping',
        'order_id' => $order->id,
    ]);

    app(CleanUpOrderLines::class)->handle($order, function ($order) {
        return $order;
    });

    assertDatabaseHas((new OrderLine)->getTable(), [
        'order_id' => $order->id,
        'purchasable_id' => $purchasable->id,
    ]);

    assertDatabaseHas((new OrderLine)->getTable(), [
        'order_id' => $order->id,
        'purchasable_id' => $purchasableB->id,
        'quantity' => 5,
        'meta' => json_encode(['foo' => 'bar']),
    ]);

    assertDatabaseMissing((new OrderLine)->getTable(), [
        'order_id' => $order->id,
        'purchasable_id' => $purchasableB->id,
        'quantity' => 15,
        'meta' => json_encode(['bar' => 'baz']),
    ]);
});
