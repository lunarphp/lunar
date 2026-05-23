<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Orders\UpdateOrderStatus;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('updates the order status and fires an event', function () {
    Event::fake();

    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    UpdateOrderStatus::run($order, 'payment-received');

    expect($order->fresh()->status)->toBe('payment-received');

    Event::assertDispatched(OrderStatusUpdated::class, fn (OrderStatusUpdated $event) => $event->order->is($order)
        && $event->previousStatus === 'awaiting-payment'
        && $event->status === 'payment-received');
});

test('does not fire an event when status is unchanged', function () {
    Event::fake();

    $order = Order::factory()->create(['status' => 'payment-received']);

    UpdateOrderStatus::run($order, 'payment-received');

    Event::assertNotDispatched(OrderStatusUpdated::class);
});

test('throws when the status is not configured', function () {
    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    UpdateOrderStatus::run($order, 'not-a-real-status');
})->throws(OrderActionException::class);
