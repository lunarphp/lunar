<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Orders\ResolveFulfilmentStatus;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\States\Order\Fulfilment\Fulfilled;
use Lunar\Core\States\Order\Fulfilment\PartiallyFulfilled;
use Lunar\Core\States\Order\Fulfilment\PartiallyReturned;
use Lunar\Core\States\Order\Fulfilment\Returned;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

function resolveFulfilment(Order $order): string
{
    return (new ResolveFulfilmentStatus)->execute($order);
}

function fulfilmentWith(Order $order, OrderLine $line, int $quantity, string $state): Fulfilment
{
    $fulfilment = Fulfilment::factory()->create([
        'order_id' => $order->id,
        'state' => $state,
    ]);

    $fulfilment->lines()->createQuietly([
        'order_line_id' => $line->id,
        'quantity' => $quantity,
    ]);

    return $fulfilment;
}

test('a digital-only order with no physical lines is fulfilled', function () {
    $order = Order::factory()->create();
    OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'digital', 'quantity' => 1]);

    expect(resolveFulfilment($order))->toBe(Fulfilled::class);
});

test('a shippable line counts as fulfillable regardless of its type', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'giftcard',
        'requires_shipping' => true,
        'quantity' => 2,
    ]);

    expect(resolveFulfilment($order))->toBe(Unfulfilled::class);

    fulfilmentWith($order, $line, 2, 'shipped');

    expect(resolveFulfilment($order))->toBe(Fulfilled::class);
});

test('an order with nothing shipped is unfulfilled', function () {
    $order = Order::factory()->create();
    OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 3]);

    expect(resolveFulfilment($order))->toBe(Unfulfilled::class);
});

test('a pending fulfilment does not count as shipped', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 3]);
    fulfilmentWith($order, $line, 3, 'pending');

    expect(resolveFulfilment($order))->toBe(Unfulfilled::class);
});

test('a partially shipped order is partially fulfilled', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 3]);
    fulfilmentWith($order, $line, 1, 'shipped');

    expect(resolveFulfilment($order))->toBe(PartiallyFulfilled::class);
});

test('a fully shipped order is fulfilled', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 3]);
    fulfilmentWith($order, $line, 3, 'shipped');

    expect(resolveFulfilment($order))->toBe(Fulfilled::class);
});

test('a partially returned order is partially returned', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 3]);
    fulfilmentWith($order, $line, 2, 'shipped');
    fulfilmentWith($order, $line, 1, 'returned');

    expect(resolveFulfilment($order))->toBe(PartiallyReturned::class);
});

test('a fully returned order is returned', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 3]);
    fulfilmentWith($order, $line, 3, 'returned');

    expect(resolveFulfilment($order))->toBe(Returned::class);
});
