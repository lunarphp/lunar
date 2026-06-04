<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\InProcess;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

class FakeShippedNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(): array
    {
        return ['mail'];
    }
}

test('notifications configured for a status are dispatched when the order enters that state', function () {
    config([
        'lunar.orders.notifications' => [
            'in-process' => [FakeShippedNotification::class],
        ],
    ]);

    NotificationFacade::fake();

    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    $order->status->transitionTo(InProcess::class);

    NotificationFacade::assertSentTo($order->fresh(), FakeShippedNotification::class);
});

test('no notifications are dispatched when none are configured', function () {
    config(['lunar.orders.notifications' => []]);

    NotificationFacade::fake();

    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    $order->status->transitionTo(InProcess::class);

    NotificationFacade::assertNothingSent();
});
