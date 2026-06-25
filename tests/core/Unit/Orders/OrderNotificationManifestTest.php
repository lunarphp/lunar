<?php

use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Notifications\OrderUpdate;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

class StubOrderNotification {}

class StubShippedNotification {}

it('ships the default order-update notification as a manual, order-scoped entry', function () {
    expect(OrderNotifications::sendable())->toBe(['order-update' => 'Order update'])
        ->and(OrderNotifications::get('order-update'))->toBe(OrderUpdate::class)
        ->and(OrderNotifications::label('order-update'))->toBe('Order update');
});

it('registers a notification with a label and class', function () {
    OrderNotifications::register('order-update', StubOrderNotification::class, 'Order update');

    expect(OrderNotifications::get('order-update'))->toBe(StubOrderNotification::class)
        ->and(OrderNotifications::label('order-update'))->toBe('Order update');
});

it('defaults the label to the key when none is given', function () {
    OrderNotifications::register('whatever', StubOrderNotification::class);

    expect(OrderNotifications::sendable())->toHaveKey('whatever')
        ->and(OrderNotifications::sendable()['whatever'])->toBe('whatever');
});

it('only lists manually-sendable entries for the matching scope', function () {
    OrderNotifications::forget('order-update');

    OrderNotifications::register('order-confirmation', StubOrderNotification::class, 'Order confirmation', on: ['placed'], manual: true, scope: NotificationScope::Order);
    OrderNotifications::register('auto-only', StubOrderNotification::class, 'Auto only', on: ['paid'], manual: false, scope: NotificationScope::Order);
    OrderNotifications::register('shipped', StubShippedNotification::class, 'Shipping update', on: ['shipped'], manual: true, scope: NotificationScope::Fulfilment);

    expect(OrderNotifications::sendable(NotificationScope::Order))->toBe(['order-confirmation' => 'Order confirmation'])
        ->and(OrderNotifications::sendable(NotificationScope::Fulfilment))->toBe(['shipped' => 'Shipping update']);
});

it('resolves auto-triggered notifications by status within a scope', function () {
    OrderNotifications::register('order-confirmation', StubOrderNotification::class, on: ['placed'], scope: NotificationScope::Order);
    OrderNotifications::register('shipped', StubShippedNotification::class, on: ['shipped'], scope: NotificationScope::Fulfilment);

    expect(OrderNotifications::triggeredBy('placed', NotificationScope::Order))->toBe([StubOrderNotification::class])
        ->and(OrderNotifications::triggeredBy('shipped', NotificationScope::Fulfilment))->toBe([StubShippedNotification::class])
        // scope guards against a name shared across machines.
        ->and(OrderNotifications::triggeredBy('shipped', NotificationScope::Order))->toBe([])
        ->and(OrderNotifications::triggeredBy('placed', NotificationScope::Fulfilment))->toBe([]);
});

it('keeps an auto-triggered notification manually sendable too, so it can be resent', function () {
    OrderNotifications::register('order-confirmation', StubOrderNotification::class, 'Order confirmation', on: ['placed'], manual: true, scope: NotificationScope::Order);

    expect(OrderNotifications::triggeredBy('placed', NotificationScope::Order))->toBe([StubOrderNotification::class])
        ->and(OrderNotifications::sendable(NotificationScope::Order))->toHaveKey('order-confirmation');
});

it('forgets a notification by key', function () {
    OrderNotifications::forget('order-update');

    expect(OrderNotifications::sendable())->toBe([])
        ->and(OrderNotifications::get('order-update'))->toBeNull();
});

it('falls back to the raw key for an unregistered label, and null for no key', function () {
    expect(OrderNotifications::get('missing'))->toBeNull()
        ->and(OrderNotifications::label('missing'))->toBe('missing')
        ->and(OrderNotifications::label(null))->toBeNull()
        ->and(OrderNotifications::label(''))->toBeNull();
});
