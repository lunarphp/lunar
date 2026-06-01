<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Orders\MarkOrderAsShipped;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\Shipped;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('transitions the order status to shipped', function () {
    $order = Order::factory()->create(['status' => 'in-process']);

    Event::fake([OrderStatusUpdated::class]);

    app(MarkOrderAsShipped::class)->execute($order);

    expect((string) $order->fresh()->status)->toBe('shipped');

    Event::assertDispatched(OrderStatusUpdated::class, fn (OrderStatusUpdated $event) => $event->newStatus instanceof Shipped);
});
