<?php

use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Notifications\OrderCancellation;
use Lunar\Core\Notifications\OrderConfirmation;
use Lunar\Core\Notifications\OrderProvisioned;
use Lunar\Core\Notifications\OrderReadyForCollection;
use Lunar\Core\Notifications\OrderShipped;
use Lunar\Core\Notifications\OrderUpdate;
use Lunar\Core\Notifications\PaymentReceived;
use Lunar\Core\Notifications\RefundIssued;
use Lunar\Core\Notifications\ReturnReceived;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

class StubOrderNotification {}

class StubShippedNotification {}

it('ships the lifecycle defaults keyed to their triggers, with the manual variants sendable', function () {
    expect(OrderNotifications::sendable())->toBe([
        'order-confirmation' => 'Order confirmation',
        'payment-received' => 'Payment received',
        'order-cancelled' => 'Order cancelled',
        'refund-issued' => 'Refund issued',
        'partial-fulfilment-update' => 'Partial fulfilment update',
        'order-update' => 'Order update',
    ])
        ->and(OrderNotifications::sendable(NotificationScope::Fulfilment))->toBe([])
        ->and(OrderNotifications::get('order-update'))->toBe(OrderUpdate::class)
        ->and(OrderNotifications::label('order-update'))->toBe('Order update')
        ->and(OrderNotifications::triggeredBy('placed'))->toBe([OrderConfirmation::class])
        ->and(OrderNotifications::triggeredBy('paid'))->toBe([PaymentReceived::class])
        ->and(OrderNotifications::triggeredBy('cancelled'))->toBe([OrderCancellation::class])
        ->and(OrderNotifications::triggeredBy('refund-issued'))->toBe([RefundIssued::class])
        // The refund event key is distinct from the payment-status rollup.
        ->and(OrderNotifications::triggeredBy('refunded'))->toBe([])
        ->and(OrderNotifications::triggeredBy('shipped', NotificationScope::Fulfilment))->toBe([OrderShipped::class])
        ->and(OrderNotifications::triggeredBy('ready-for-collection', NotificationScope::Fulfilment))->toBe([OrderReadyForCollection::class])
        ->and(OrderNotifications::triggeredBy('provisioned', NotificationScope::Fulfilment))->toBe([OrderProvisioned::class])
        ->and(OrderNotifications::triggeredBy('returned', NotificationScope::Fulfilment))->toBe([ReturnReceived::class])
        // Per-fulfilment states only: the order rollups never double-send.
        ->and(OrderNotifications::triggeredBy('fulfilled'))->toBe([])
        ->and(OrderNotifications::triggeredBy('returned'))->toBe([])
        ->and(OrderNotifications::triggeredBy('collected', NotificationScope::Fulfilment))->toBe([]);
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
    OrderNotifications::forget(...array_keys(OrderNotifications::sendable()));

    OrderNotifications::register('order-confirmation', StubOrderNotification::class, 'Order confirmation', on: ['placed'], manual: true, scope: NotificationScope::Order);
    OrderNotifications::register('auto-only', StubOrderNotification::class, 'Auto only', on: ['paid'], manual: false, scope: NotificationScope::Order);
    OrderNotifications::register('shipped', StubShippedNotification::class, 'Shipping update', on: ['shipped'], manual: true, scope: NotificationScope::Fulfilment);

    expect(OrderNotifications::sendable(NotificationScope::Order))->toBe(['order-confirmation' => 'Order confirmation'])
        ->and(OrderNotifications::sendable(NotificationScope::Fulfilment))->toBe(['shipped' => 'Shipping update']);
});

it('resolves auto-triggered notifications by status within a scope', function () {
    OrderNotifications::forget('order-shipped');
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

    expect(OrderNotifications::sendable())->not->toHaveKey('order-update')
        ->and(OrderNotifications::get('order-update'))->toBeNull();
});

it('falls back to the raw key for an unregistered label, and null for no key', function () {
    expect(OrderNotifications::get('missing'))->toBeNull()
        ->and(OrderNotifications::label('missing'))->toBe('missing')
        ->and(OrderNotifications::label(null))->toBeNull()
        ->and(OrderNotifications::label(''))->toBeNull();
});
