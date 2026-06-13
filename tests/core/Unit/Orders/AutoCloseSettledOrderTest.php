<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

function autoCloseOrder(int $total = 1000): array
{
    $order = Order::factory()->create(['total' => $total]);
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 1]);

    return [$order, $line];
}

function autoCloseCapture(Order $order, int $amount): void
{
    $order->transactions()->create([
        'type' => 'capture', 'success' => true, 'amount' => $amount,
        'driver' => 'lunar', 'reference' => uniqid(), 'status' => 'settled',
    ]);
}

test('it auto-closes an order once fully paid and fulfilled when enabled', function () {
    config(['lunar.orders.auto_close' => true]);
    [$order, $line] = autoCloseOrder();

    autoCloseCapture($order, 1000);
    // Paid but not yet fulfilled — stays open.
    expect($order->refresh()->isClosed())->toBeFalse();

    $order->createFulfilment([$line->id => 1])->ship();

    expect($order->refresh())
        ->isClosed()->toBeTrue()
        ->and((string) $order->payment_status)->toBe('paid')
        ->and((string) $order->fulfilment_status)->toBe('fulfilled');
});

test('it leaves a settled order open when the preference is off (default)', function () {
    [$order, $line] = autoCloseOrder();

    autoCloseCapture($order, 1000);
    $order->createFulfilment([$line->id => 1])->ship();

    expect($order->refresh()->isClosed())->toBeFalse();
});

test('it does not close a paid order that is not yet fulfilled', function () {
    config(['lunar.orders.auto_close' => true]);
    [$order] = autoCloseOrder();

    autoCloseCapture($order, 1000);

    expect($order->refresh())
        ->isClosed()->toBeFalse()
        ->and((string) $order->payment_status)->toBe('paid');
});

test('it does not close a fulfilled order that is not yet paid', function () {
    config(['lunar.orders.auto_close' => true]);
    [$order, $line] = autoCloseOrder();

    $order->createFulfilment([$line->id => 1])->ship();

    expect($order->refresh())
        ->isClosed()->toBeFalse()
        ->and((string) $order->fulfilment_status)->toBe('fulfilled');
});

test('it does not reopen an auto-closed order that is later returned', function () {
    config(['lunar.orders.auto_close' => true]);
    [$order, $line] = autoCloseOrder();

    autoCloseCapture($order, 1000);
    $fulfilment = $order->createFulfilment([$line->id => 1]);
    $fulfilment->ship();
    expect($order->refresh()->isClosed())->toBeTrue();

    $fulfilment->markReturned();

    expect($order->refresh())
        ->isClosed()->toBeTrue()
        ->and((string) $order->fulfilment_status)->toBe('returned');
});
