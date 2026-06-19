<?php

use Lunar\Core\Facades\CustomerNotifications;
use Lunar\Core\Notifications\OrderUpdate;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

class StubCustomerNotification {}

it('ships the default order-update notification', function () {
    expect(CustomerNotifications::isEmpty())->toBeFalse()
        ->and(CustomerNotifications::all())->toBe(['order-update' => 'Order update'])
        ->and(CustomerNotifications::get('order-update'))->toBe(OrderUpdate::class);
});

it('is empty once the defaults are forgotten', function () {
    CustomerNotifications::forget('order-update');

    expect(CustomerNotifications::isEmpty())->toBeTrue()
        ->and(CustomerNotifications::all())->toBe([]);
});

it('registers a notification, exposing its label and class', function () {
    CustomerNotifications::register('order-update', StubCustomerNotification::class, 'Order update');

    expect(CustomerNotifications::isEmpty())->toBeFalse()
        ->and(CustomerNotifications::all())->toBe(['order-update' => 'Order update'])
        ->and(CustomerNotifications::get('order-update'))->toBe(StubCustomerNotification::class)
        ->and(CustomerNotifications::label('order-update'))->toBe('Order update');
});

it('defaults the label to the key when none is given', function () {
    CustomerNotifications::register('order-update', StubCustomerNotification::class);

    expect(CustomerNotifications::all())->toBe(['order-update' => 'order-update']);
});

it('returns null for an unregistered class and falls back to the raw key for its label', function () {
    expect(CustomerNotifications::get('missing'))->toBeNull()
        ->and(CustomerNotifications::label('missing'))->toBe('missing')
        ->and(CustomerNotifications::label(null))->toBeNull()
        ->and(CustomerNotifications::label(''))->toBeNull();
});

it('forgets a notification by key', function () {
    CustomerNotifications::register('order-update', StubCustomerNotification::class, 'Order update');
    CustomerNotifications::forget('order-update');

    expect(CustomerNotifications::isEmpty())->toBeTrue()
        ->and(CustomerNotifications::get('order-update'))->toBeNull();
});
