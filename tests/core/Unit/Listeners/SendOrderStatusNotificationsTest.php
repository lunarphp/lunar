<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
    Location::factory()->default()->create();
});

class FakePaidNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(): array
    {
        return ['mail'];
    }
}

class FakeFulfilledNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(): array
    {
        return ['mail'];
    }
}

class FakeCancelledNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(): array
    {
        return ['mail'];
    }
}

test('notifications configured for a payment status are dispatched when the order reaches it', function () {
    config([
        'lunar.orders.notifications' => [
            'paid' => [FakePaidNotification::class],
        ],
    ]);

    NotificationFacade::fake();

    $order = Order::factory()->create();

    $order->transactions()->create([
        'type' => 'capture', 'success' => true, 'amount' => $order->total,
        'driver' => 'lunar', 'reference' => uniqid(), 'status' => 'settled',
    ]);

    NotificationFacade::assertSentTo($order->fresh(), FakePaidNotification::class);
});

test('no notifications are dispatched when none are configured', function () {
    config(['lunar.orders.notifications' => []]);

    NotificationFacade::fake();

    $order = Order::factory()->create();

    $order->transactions()->create([
        'type' => 'capture', 'success' => true, 'amount' => $order->total,
        'driver' => 'lunar', 'reference' => uniqid(), 'status' => 'settled',
    ]);

    NotificationFacade::assertNothingSent();
});

test('notifications configured for a fulfilment status are dispatched when the order reaches it', function () {
    config([
        'lunar.orders.notifications' => [
            'fulfilled' => [FakeFulfilledNotification::class],
        ],
    ]);

    NotificationFacade::fake();

    $order = Order::factory()->create();
    $line = OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'physical',
        'quantity' => 1,
    ]);

    $order->createFulfilment([$line->id => 1])->ship();

    NotificationFacade::assertSentTo($order->fresh(), FakeFulfilledNotification::class);
});

test('a cancelled notification is dispatched when the order is cancelled with notify', function () {
    config([
        'lunar.orders.notifications' => [
            'cancelled' => [FakeCancelledNotification::class],
        ],
    ]);

    NotificationFacade::fake();

    $order = Order::factory()->create();
    OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'physical',
        'quantity' => 1,
    ]);

    $order->cancel(notify: true);

    NotificationFacade::assertSentTo($order->fresh(), FakeCancelledNotification::class);
});

test('a cancelled notification is suppressed when the order is cancelled without notify', function () {
    config([
        'lunar.orders.notifications' => [
            'cancelled' => [FakeCancelledNotification::class],
        ],
    ]);

    NotificationFacade::fake();

    $order = Order::factory()->create();
    OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'physical',
        'quantity' => 1,
    ]);

    $order->cancel(notify: false);

    NotificationFacade::assertNotSentTo($order->fresh(), FakeCancelledNotification::class);
});
