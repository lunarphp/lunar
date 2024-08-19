<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Exceptions\NonPurchasableItemException;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\TestPurchasable;
use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make an order line', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => ProductVariant::factory()->create()->id,
    ];

    OrderLine::factory()->create($data);

    $this->assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );
});

test('check unit price casts correctly', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => ProductVariant::factory()->create()->id,
        'unit_price' => 507,
        'unit_quantity' => 100,
    ];

    $orderLine = OrderLine::factory()->create($data);

    $this->assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->unit_price->decimal)->toEqual(5.07);
    expect($orderLine->unit_price->unitDecimal)->toEqual(0.05);
    expect($orderLine->unit_price->unitDecimal(false))->toEqual(0.0507);
});

test('only purchasables can be added to an order', function () {
    $order = Order::factory()->create();

    $this->expectException(NonPurchasableItemException::class);

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => Channel::class,
        'purchasable_id' => Channel::factory()->create()->id,
    ];

    OrderLine::factory()->create($data);

    $this->assertDatabaseMissing((new CartLine)->getTable(), $data);
});

test('non eloquent models can be added to an order', function () {
    $order = Order::factory()->create();

    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $taxClass = \Lunar\Models\TaxClass::factory()->create();

    $shippingOption = new \Lunar\DataTypes\ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new \Lunar\DataTypes\Price(500, $currency, 1),
        taxClass: $taxClass
    );

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'type' => $shippingOption->getType(),
        'purchasable_type' => \Lunar\DataTypes\ShippingOption::class,
        'purchasable_id' => $shippingOption->getIdentifier(),
        'unit_price' => $shippingOption->getPrice()->value,
        'unit_quantity' => $shippingOption->getUnitQuantity(),
    ];

    $orderLine = OrderLine::factory()->create($data);

    assertDatabaseHas(
        (new OrderLine())->getTable(),
        $data
    );

    expect($orderLine->unit_price->decimal)->toEqual(5.0)
        ->and($orderLine->unit_price->unitDecimal)->toEqual(5.0);

    $testPurchasable = new TestPurchasable(
        name: 'Test Purchasable',
        description: 'Test Purchasable',
        identifier: 'TESTPUR',
        price: new \Lunar\DataTypes\Price(650, $currency, 1),
        taxClass: $taxClass
    );

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'type' => $testPurchasable->getType(),
        'purchasable_type' => TestPurchasable::class,
        'purchasable_id' => $testPurchasable->getIdentifier(),
        'unit_price' => $testPurchasable->getPrice()->value,
        'unit_quantity' => $testPurchasable->getUnitQuantity(),
    ];

    $orderLine = OrderLine::factory()->create($data);

    assertDatabaseHas(
        (new OrderLine())->getTable(),
        $data
    );

    expect($orderLine->unit_price->decimal)->toEqual(6.5)
        ->and($orderLine->unit_price->unitDecimal)
        ->toEqual(6.5);
});
