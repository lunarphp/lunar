<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\InProcess;
use Lunar\Core\States\Order\Order\OnHold;
use Lunar\Core\States\Order\Order\PartiallyShipped;
use Lunar\Core\States\Order\Order\Refunded as OrderRefunded;
use Lunar\Core\States\Order\Order\Shipped;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

function placedOrder(int $quantity, int $total = 1000): array
{
    $order = Order::factory()->create(['status' => 'awaiting-payment', 'total' => $total]);
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

test('a fresh order is awaiting payment, pending and unfulfilled', function () {
    [$order] = placedOrder(2);

    expect($order->status)->toBeInstanceOf(AwaitingPayment::class)
        ->and((string) $order->payment_status)->toBe('pending')
        ->and((string) $order->fulfilment_status)->toBe('unfulfilled');
});

test('capturing payment derives in-process', function () {
    [$order] = placedOrder(2);

    capture($order, 1000);

    $order->refresh();
    expect((string) $order->payment_status)->toBe('paid')
        ->and($order->status)->toBeInstanceOf(InProcess::class);
});

test('shipping part then all of an order derives partially-shipped then shipped', function () {
    [$order, $line] = placedOrder(2);
    capture($order, 1000);

    Fulfilments::ship(Fulfilments::create($order, [$line->id => 1]));
    $order->refresh();
    expect((string) $order->fulfilment_status)->toBe('partially-fulfilled')
        ->and($order->status)->toBeInstanceOf(PartiallyShipped::class);

    Fulfilments::ship(Fulfilments::create($order, [$line->id => 1]));
    $order->refresh();
    expect((string) $order->fulfilment_status)->toBe('fulfilled')
        ->and($order->status)->toBeInstanceOf(Shipped::class);
});

test('a full refund derives the refunded override', function () {
    [$order] = placedOrder(1);
    capture($order, 1000);

    $order->transactions()->create([
        'type' => 'refund', 'success' => true, 'amount' => 1000,
        'driver' => 'lunar', 'reference' => uniqid(), 'status' => 'refunded',
    ]);

    $order->refresh();
    expect((string) $order->payment_status)->toBe('refunded')
        ->and($order->status)->toBeInstanceOf(OrderRefunded::class);
});

test('a manual override suppresses derivation until resumed', function () {
    [$order, $line] = placedOrder(1);
    capture($order, 1000);

    $order->refresh()->status->transitionTo(OnHold::class);
    expect($order->fresh()->status)->toBeInstanceOf(OnHold::class);

    // Ship everything while on hold — the headline stays put, but the derived
    // fulfilment column still tracks reality.
    Fulfilments::ship(Fulfilments::create($order, [$line->id => 1]));
    $order->refresh();
    expect($order->status)->toBeInstanceOf(OnHold::class)
        ->and((string) $order->fulfilment_status)->toBe('fulfilled');

    // Resuming re-derives from the rollups rather than trusting the literal target.
    $order->status->transitionTo(InProcess::class);
    expect($order->fresh()->status)->toBeInstanceOf(Shipped::class);
});

test('payment status change dispatches OrderPaymentStatusUpdated', function () {
    [$order] = placedOrder(1);

    Event::fake([OrderPaymentStatusUpdated::class]);

    capture($order, 1000);

    Event::assertDispatched(OrderPaymentStatusUpdated::class, function (OrderPaymentStatusUpdated $event) {
        return (string) $event->newStatus === 'paid';
    });
});
