<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

function placedOrder(int $quantity, int $total = 1000): array
{
    $order = Order::factory()->create(['total' => $total]);
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => $quantity]);

    return [$order, $line];
}

function capture(Order $order, int $amount): void
{
    $order->transactions()->create([
        'type' => 'capture', 'success' => true, 'amount' => $amount,
        'driver' => 'lunar', 'reference' => uniqid(), 'status' => 'settled',
    ]);
}

test('a fresh order is pending and unfulfilled', function () {
    [$order] = placedOrder(2);

    expect((string) $order->payment_status)->toBe('pending')
        ->and((string) $order->fulfilment_status)->toBe('unfulfilled');
});

test('capturing payment derives the paid payment status', function () {
    [$order] = placedOrder(2);

    capture($order, 1000);

    expect((string) $order->refresh()->payment_status)->toBe('paid');
});

test('shipping part then all of an order derives partially-fulfilled then fulfilled', function () {
    [$order, $line] = placedOrder(2);
    capture($order, 1000);

    $order->createFulfilment([$line->id => 1])->ship();
    expect((string) $order->refresh()->fulfilment_status)->toBe('partially-fulfilled');

    $order->createFulfilment([$line->id => 1])->ship();
    expect((string) $order->refresh()->fulfilment_status)->toBe('fulfilled');
});

test('a full refund derives the refunded payment status', function () {
    [$order] = placedOrder(1);
    capture($order, 1000);

    $order->transactions()->create([
        'type' => 'refund', 'success' => true, 'amount' => 1000,
        'driver' => 'lunar', 'reference' => uniqid(), 'status' => 'refunded',
    ]);

    expect((string) $order->refresh()->payment_status)->toBe('refunded');
});

test('payment status change dispatches OrderPaymentStatusUpdated', function () {
    [$order] = placedOrder(1);

    Event::fake([OrderPaymentStatusUpdated::class]);

    capture($order, 1000);

    Event::assertDispatched(
        OrderPaymentStatusUpdated::class,
        fn (OrderPaymentStatusUpdated $event) => (string) $event->newStatus === 'paid',
    );
});

test('fulfilment status change dispatches OrderFulfilmentStatusUpdated', function () {
    [$order, $line] = placedOrder(1);

    Event::fake([OrderFulfilmentStatusUpdated::class]);

    $order->createFulfilment([$line->id => 1])->ship();

    Event::assertDispatched(
        OrderFulfilmentStatusUpdated::class,
        fn (OrderFulfilmentStatusUpdated $event) => (string) $event->newStatus === 'fulfilled',
    );
});
