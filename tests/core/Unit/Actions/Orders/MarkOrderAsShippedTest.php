<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Orders\MarkOrderAsShipped;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
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

test('marks the order as shipped using the configured status', function () {
    Event::fake();

    $order = Order::factory()->create(['status' => 'payment-received']);

    app(MarkOrderAsShipped::class)->execute($order);

    expect($order->fresh()->status)->toBe('dispatched');

    Event::assertDispatched(OrderStatusUpdated::class, fn (OrderStatusUpdated $event) => $event->status === 'dispatched');
});

test('respects the configured shipped_status override', function () {
    config(['lunar.orders.shipped_status' => 'payment-offline']);

    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    app(MarkOrderAsShipped::class)->execute($order);

    expect($order->fresh()->status)->toBe('payment-offline');
});
