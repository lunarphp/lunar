<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

class FakePaidNotification extends Notification
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
