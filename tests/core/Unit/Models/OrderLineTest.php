<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Exceptions\NonPurchasableItemException;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;

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
