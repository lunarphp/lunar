<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Orders\CancelOrder;
use Lunar\Core\Events\Orders\OrderCancelled;
use Lunar\Core\Exceptions\OrderActionException;
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

    $this->order = Order::factory()->create();
    $this->line = OrderLine::factory()->create([
        'order_id' => $this->order->id,
        'type' => 'physical',
        'quantity' => 2,
    ]);
});

function cancel(Order $order, ?string $reason = null, ?string $note = null, bool $notify = true): Order
{
    return $order->cancel($reason, $note, $notify);
}

it('cancels an unfulfilled order, closing it and voiding its parcels', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    cancel($this->order, 'items-unavailable', 'Out of stock');

    $this->order->refresh();
    expect($this->order->isCancelled())->toBeTrue()
        ->and($this->order->cancel_reason)->toBe('items-unavailable')
        ->and($this->order->cancel_note)->toBe('Out of stock')
        ->and($this->order->isClosed())->toBeTrue()
        ->and($this->order->lifecycleStatus())->toBe('cancelled')
        ->and((string) $fulfilment->refresh()->state)->toBe('cancelled');
});

it('dispatches OrderCancelled with the notify flag', function () {
    $this->order->createFulfilment([$this->line->id => 2]);
    Event::fake([OrderCancelled::class]);

    cancel($this->order, notify: false);

    Event::assertDispatched(OrderCancelled::class, fn (OrderCancelled $e) => $e->notify === false);
});

it('cannot cancel an order that has shipped', function () {
    $this->order->createFulfilment([$this->line->id => 2])->ship();

    expect(CancelOrder::canRun($this->order))->toBeFalse();

    cancel($this->order);
})->throws(OrderActionException::class);

it('cannot cancel an already-cancelled order', function () {
    $this->order->createFulfilment([$this->line->id => 2]);
    cancel($this->order);

    expect(CancelOrder::canRun($this->order->refresh()))->toBeFalse();
});
