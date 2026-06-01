<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Orders\UpdateOrderStatus;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\OnHold;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('updates the order status and fires an event', function () {
    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    Event::fake([OrderStatusUpdated::class]);

    app(UpdateOrderStatus::class)->execute($order, 'on-hold');

    expect((string) $order->fresh()->status)->toBe('on-hold');

    Event::assertDispatched(OrderStatusUpdated::class, fn (OrderStatusUpdated $event) => $event->order->is($order)
        && $event->previousStatus instanceof AwaitingPayment
        && $event->newStatus instanceof OnHold);
});

test('does not fire an event when status is unchanged', function () {
    $order = Order::factory()->create(['status' => 'on-hold']);

    Event::fake([OrderStatusUpdated::class]);

    app(UpdateOrderStatus::class)->execute($order, 'on-hold');

    Event::assertNotDispatched(OrderStatusUpdated::class);
});

test('throws when the status is not a registered OrderState', function () {
    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    app(UpdateOrderStatus::class)->execute($order, 'not-a-real-status');
})->throws(OrderActionException::class);
