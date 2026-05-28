<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Fulfilment\Processing;
use Lunar\Core\States\Order\Fulfilment\Shipped as FulfilmentShipped;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\OnHold;
use Lunar\Core\States\Order\Payment\Captured;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('changing payment_status recomputes order_status', function () {
    $order = Order::factory()->create([
        'payment_status' => 'pending',
        'fulfilment_status' => 'unfulfilled',
        'order_status' => 'awaiting-payment',
    ]);

    $order->payment_status->transitionTo(Captured::class);

    expect((string) $order->fresh()->order_status)->toBe('in-process');
});

test('changing fulfilment_status recomputes order_status', function () {
    $order = Order::factory()->create([
        'payment_status' => 'captured',
        'fulfilment_status' => 'processing',
        'order_status' => 'in-process',
    ]);

    $order->fulfilment_status->transitionTo(FulfilmentShipped::class);

    expect((string) $order->fresh()->order_status)->toBe('shipped');
});

test('dispatches OrderStatusUpdated exactly once when both columns change', function () {
    $order = Order::factory()->create([
        'payment_status' => 'pending',
        'fulfilment_status' => 'unfulfilled',
        'order_status' => 'awaiting-payment',
    ]);

    Event::fake([OrderStatusUpdated::class]);

    $order->payment_status->transitionTo(Captured::class);

    Event::assertDispatchedTimes(OrderStatusUpdated::class, 1);
});

test('OnHold blocks recomputation', function () {
    $order = Order::factory()->create([
        'payment_status' => 'pending',
        'fulfilment_status' => 'unfulfilled',
        'order_status' => OnHold::$name,
    ]);

    $order->payment_status->transitionTo(Captured::class);

    expect((string) $order->fresh()->order_status)->toBe('on-hold');
});

test('Cancelled blocks recomputation', function () {
    $order = Order::factory()->create([
        'payment_status' => 'pending',
        'fulfilment_status' => 'unfulfilled',
        'order_status' => Cancelled::$name,
    ]);

    $order->fulfilment_status->transitionTo(Processing::class);

    expect((string) $order->fresh()->order_status)->toBe('cancelled');
});

test('transitioning out of OnHold resumes computation', function () {
    $order = Order::factory()->create([
        'payment_status' => 'captured',
        'fulfilment_status' => 'shipped',
        'order_status' => OnHold::$name,
    ]);

    // Manually transition order_status out of the override
    $order->forceFill(['order_status' => 'awaiting-payment'])->save();

    expect((string) $order->fresh()->order_status)->toBe('shipped');
});
