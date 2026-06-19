<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
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

class FakeShippedNotification extends Notification
{
    public function __construct(public Fulfilment $fulfilment) {}

    public function via(): array
    {
        return ['mail'];
    }
}

class FakeOrderFulfilledNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(): array
    {
        return ['mail'];
    }
}

class FakeReturnedNotification extends Notification
{
    public function __construct(public Fulfilment $fulfilment) {}

    public function via(): array
    {
        return ['mail'];
    }
}

class FakeParcelCancelledNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(): array
    {
        return ['mail'];
    }
}

function shippableOrder(): Order
{
    $order = Order::factory()->create();
    OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'physical',
        'quantity' => 1,
    ]);

    return $order;
}

test('a per-parcel notification configured for the shipped state is dispatched, carrying the fulfilment', function () {
    OrderNotifications::register('shipped', FakeShippedNotification::class, on: ['shipped'], scope: NotificationScope::Fulfilment);

    NotificationFacade::fake();

    $order = shippableOrder();
    $fulfilment = $order->createFulfilment([$order->lines->first()->id => 1])->ship();

    NotificationFacade::assertSentTo(
        $order->fresh(),
        FakeShippedNotification::class,
        fn (FakeShippedNotification $notification) => $notification->fulfilment->is($fulfilment),
    );
});

test('the per-parcel notification is suppressed when the ship asks not to notify', function () {
    OrderNotifications::register('shipped', FakeShippedNotification::class, on: ['shipped'], scope: NotificationScope::Fulfilment);

    NotificationFacade::fake();

    $order = shippableOrder();
    $order->createFulfilment([$order->lines->first()->id => 1])->ship(notify: false);

    NotificationFacade::assertNotSentTo($order->fresh(), FakeShippedNotification::class);
});

test('suppressing notify on the final ship suppresses both the per-parcel and the order rollup email', function () {
    OrderNotifications::register('shipped', FakeShippedNotification::class, on: ['shipped'], scope: NotificationScope::Fulfilment);
    OrderNotifications::register('fulfilled', FakeOrderFulfilledNotification::class, on: ['fulfilled']);

    NotificationFacade::fake();

    $order = shippableOrder();
    $order->createFulfilment([$order->lines->first()->id => 1])->ship(notify: false);

    NotificationFacade::assertNotSentTo($order->fresh(), FakeShippedNotification::class);
    NotificationFacade::assertNotSentTo($order->fresh(), FakeOrderFulfilledNotification::class);
});

test('both the per-parcel and the order rollup email fire when notify is left on', function () {
    OrderNotifications::register('shipped', FakeShippedNotification::class, on: ['shipped'], scope: NotificationScope::Fulfilment);
    OrderNotifications::register('fulfilled', FakeOrderFulfilledNotification::class, on: ['fulfilled']);

    NotificationFacade::fake();

    $order = shippableOrder();
    $order->createFulfilment([$order->lines->first()->id => 1])->ship();

    NotificationFacade::assertSentTo($order->fresh(), FakeShippedNotification::class);
    NotificationFacade::assertSentTo($order->fresh(), FakeOrderFulfilledNotification::class);
});

test('a per-parcel notification configured for the returned state fires on markReturned, carrying the fulfilment', function () {
    OrderNotifications::register('returned', FakeReturnedNotification::class, on: ['returned'], scope: NotificationScope::Fulfilment);

    NotificationFacade::fake();

    $order = Order::factory()->create();
    $lineA = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 1]);
    $lineB = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 1]);

    // Ship both, return only one: the order rolls up to 'partially-returned'
    // (not 'returned'), so the configured key matches only the per-parcel
    // listener — keeping this an isolated test of the return path.
    $order->createFulfilment([$lineB->id => 1])->ship();
    $returned = $order->createFulfilment([$lineA->id => 1])->ship();
    $returned->markReturned();

    NotificationFacade::assertSentTo(
        $order->fresh(),
        FakeReturnedNotification::class,
        fn (FakeReturnedNotification $notification) => $notification->fulfilment->is($returned),
    );
});

test('markReturned suppresses the returned notification when asked not to notify', function () {
    OrderNotifications::register('returned', FakeReturnedNotification::class, on: ['returned'], scope: NotificationScope::Fulfilment);

    NotificationFacade::fake();

    $order = shippableOrder();
    $order->createFulfilment([$order->lines->first()->id => 1])->ship()->markReturned(notify: false);

    NotificationFacade::assertNotSentTo($order->fresh(), FakeReturnedNotification::class);
});

test('cancelling an order does not fire the per-parcel cancelled notification when voiding its parcels', function () {
    // Shares its name with the per-parcel Cancelled state, but registered
    // order-scoped: the scope guard keeps the void path from routing it
    // through the per-parcel listener (which would double-send).
    OrderNotifications::register('cancelled', FakeParcelCancelledNotification::class, on: ['cancelled'], scope: NotificationScope::Order);

    NotificationFacade::fake();

    $order = shippableOrder();
    $order->createFulfilment([$order->lines->first()->id => 1]);

    $order->cancel(notify: true);

    // Exactly one send — the order-level one, carrying the Order.
    NotificationFacade::assertSentToTimes($order->fresh(), FakeParcelCancelledNotification::class, 1);
    NotificationFacade::assertSentTo(
        $order->fresh(),
        FakeParcelCancelledNotification::class,
        fn (FakeParcelCancelledNotification $notification) => $notification->order->is($order),
    );
});
