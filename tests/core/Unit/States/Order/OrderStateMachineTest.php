<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\Complete;
use Lunar\Core\States\Order\Order\InProcess;
use Lunar\Core\States\Order\Order\Shipped;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('a new order defaults to awaiting payment', function () {
    $order = Order::factory()->create();

    expect($order->status)->toBeInstanceOf(AwaitingPayment::class);
});

test('an order transitions through the lifecycle', function () {
    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    $order->status->transitionTo(InProcess::class);
    expect((string) $order->fresh()->status)->toBe('in-process');

    $order->refresh()->status->transitionTo(Shipped::class);
    expect((string) $order->fresh()->status)->toBe('shipped');

    $order->refresh()->status->transitionTo(Complete::class);
    expect((string) $order->fresh()->status)->toBe('complete');
});

test('an illegal transition throws and leaves the status unchanged', function () {
    $order = Order::factory()->create(['status' => 'cancelled']);

    expect(fn () => $order->status->transitionTo(AwaitingPayment::class))
        ->toThrow(CouldNotPerformTransition::class);

    expect((string) $order->fresh()->status)->toBe('cancelled');
});

test('refunded is terminal', function () {
    $order = Order::factory()->create(['status' => 'refunded']);

    expect(fn () => $order->status->transitionTo(Complete::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('changing status dispatches OrderStatusUpdated exactly once', function () {
    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    Event::fake([OrderStatusUpdated::class]);

    $order->status->transitionTo(InProcess::class);

    Event::assertDispatchedTimes(OrderStatusUpdated::class, 1);
    Event::assertDispatched(OrderStatusUpdated::class, function (OrderStatusUpdated $event) {
        return $event->previousStatus instanceof AwaitingPayment
            && $event->newStatus instanceof InProcess;
    });
});

test('a save that does not change status dispatches nothing', function () {
    $order = Order::factory()->create(['status' => 'in-process']);

    Event::fake([OrderStatusUpdated::class]);

    $order->update(['notes' => 'updated note']);

    Event::assertNotDispatched(OrderStatusUpdated::class);
});

test('cancelling an order is reflected in its status', function () {
    $order = Order::factory()->create(['status' => 'in-process']);

    $order->status->transitionTo(Cancelled::class);

    expect((string) $order->fresh()->status)->toBe('cancelled');
});
