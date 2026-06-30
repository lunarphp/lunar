<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Exceptions\NonPurchasableItemException;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\Stubs\TestPurchasable;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

test('can make an order line', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $variant = ProductVariant::factory()->create();

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
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

    $variant = ProductVariant::factory()->create();

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'unit_price' => 507,
        'unit_quantity' => 100,
    ];

    $orderLine = OrderLine::factory()->create($data);

    $this->assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->decimal('unit_price'))->toEqual(5.07);
});

test('only purchasables can be added to an order', function () {
    $order = Order::factory()->create();

    $this->expectException(NonPurchasableItemException::class);

    $channel = Channel::factory()->create();

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'purchasable_type' => $channel->getMorphClass(),
        'purchasable_id' => $channel->id,
    ];

    OrderLine::factory()->create($data);

    $this->assertDatabaseMissing((new CartLine)->getTable(), $data);
});

test('non eloquent models can be added to an order', function () {
    $order = Order::factory()->create();

    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $taxClass = TaxClass::factory()->create();

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceValue(500, $currency),
        taxClass: $taxClass
    );

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'type' => $shippingOption->getType(),
        'purchasable_type' => ShippingOption::class,
        'purchasable_id' => $shippingOption->getIdentifier(),
        'unit_price' => $shippingOption->getPrice()->value,
        'unit_quantity' => $shippingOption->getUnitQuantity(),
    ];

    $orderLine = OrderLine::factory()->create($data);

    assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->decimal('unit_price'))->toEqual(5.0);

    $testPurchasable = new TestPurchasable(
        name: 'Test Purchasable',
        description: 'Test Purchasable',
        identifier: 'TESTPUR',
        price: new PriceValue(650, $currency),
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
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->decimal('unit_price'))->toEqual(6.5);
});

test('self-describing lines can be added to an order without a purchasable', function () {
    $order = Order::factory()->create();

    Currency::factory()->create([
        'default' => true,
    ]);

    $data = [
        'order_id' => $order->id,
        'quantity' => 1,
        'type' => 'shipping',
        'purchasable_type' => null,
        'purchasable_id' => null,
        'description' => 'Basic Delivery',
        'identifier' => 'BASDEL',
        'unit_price' => 500,
    ];

    $orderLine = OrderLine::factory()->create($data);

    assertDatabaseHas(
        (new OrderLine)->getTable(),
        $data
    );

    expect($orderLine->purchasable)->toBeNull();
});

test('withoutFulfilment scope returns only lines not allocated to a fulfilment', function () {
    $unallocated = OrderLine::factory()->create();

    $allocated = OrderLine::factory()->create();
    FulfilmentLine::factory()->create([
        'order_line_id' => $allocated->id,
    ]);

    $results = OrderLine::withoutFulfilment()->pluck('id');

    expect($results)
        ->toContain($unallocated->id)
        ->not->toContain($allocated->id);
});

test('withoutFulfilment scope excludes a partially allocated line', function () {
    // The line is for 5 units but only 3 are allocated to a fulfilment. It
    // still has a fulfilment line, so it counts as allocated and is excluded.
    $line = OrderLine::factory()->create([
        'quantity' => 5,
    ]);

    FulfilmentLine::factory()->create([
        'order_line_id' => $line->id,
        'quantity' => 3,
    ]);

    expect(OrderLine::withoutFulfilment()->pluck('id'))
        ->not->toContain($line->id);
});
