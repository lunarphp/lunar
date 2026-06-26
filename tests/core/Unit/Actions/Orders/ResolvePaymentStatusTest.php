<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Orders\ResolvePaymentStatus;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Core\States\Order\Payment\PartiallyPaid;
use Lunar\Core\States\Order\Payment\PartiallyRefunded;
use Lunar\Core\States\Order\Payment\Pending;
use Lunar\Core\States\Order\Payment\Refunded;
use Lunar\Core\States\Order\Payment\Voided;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

function resolvePayment(Order $order): string
{
    return (new ResolvePaymentStatus)->execute($order);
}

test('an order with no transactions is pending', function () {
    $order = Order::factory()->create(['total' => 1000]);

    expect(resolvePayment($order))->toBe(Pending::class);
});

test('a successful intent with no capture is authorized', function () {
    $order = Order::factory()->create(['total' => 1000]);
    $order->transactions()->createQuietly(['type' => 'intent', 'success' => true, 'amount' => 1000, 'driver' => 'lunar', 'reference' => 'A', 'status' => 'authorized']);

    expect(resolvePayment($order))->toBe(Authorized::class);
});

test('a partial capture is partially paid', function () {
    $order = Order::factory()->create(['total' => 1000]);
    $order->transactions()->createQuietly(['type' => 'capture', 'success' => true, 'amount' => 400, 'driver' => 'lunar', 'reference' => 'C', 'status' => 'settled']);

    expect(resolvePayment($order))->toBe(PartiallyPaid::class);
});

test('a full capture is paid', function () {
    $order = Order::factory()->create(['total' => 1000]);
    $order->transactions()->createQuietly(['type' => 'capture', 'success' => true, 'amount' => 1000, 'driver' => 'lunar', 'reference' => 'C', 'status' => 'settled']);

    expect(resolvePayment($order))->toBe(Paid::class);
});

test('a partial refund against a full capture is partially refunded', function () {
    $order = Order::factory()->create(['total' => 1000]);
    $order->transactions()->createQuietly(['type' => 'capture', 'success' => true, 'amount' => 1000, 'driver' => 'lunar', 'reference' => 'C', 'status' => 'settled']);
    $order->transactions()->createQuietly(['type' => 'refund', 'success' => true, 'amount' => 300, 'driver' => 'lunar', 'reference' => 'R', 'status' => 'refunded']);

    expect(resolvePayment($order))->toBe(PartiallyRefunded::class);
});

test('a full refund is refunded', function () {
    $order = Order::factory()->create(['total' => 1000]);
    $order->transactions()->createQuietly(['type' => 'capture', 'success' => true, 'amount' => 1000, 'driver' => 'lunar', 'reference' => 'C', 'status' => 'settled']);
    $order->transactions()->createQuietly(['type' => 'refund', 'success' => true, 'amount' => 1000, 'driver' => 'lunar', 'reference' => 'R', 'status' => 'refunded']);

    expect(resolvePayment($order))->toBe(Refunded::class);
});

test('a failed-only ledger is voided', function () {
    $order = Order::factory()->create(['total' => 1000]);
    $order->transactions()->createQuietly(['type' => 'intent', 'success' => false, 'amount' => 1000, 'driver' => 'lunar', 'reference' => 'F', 'status' => 'failed']);

    expect(resolvePayment($order))->toBe(Voided::class);
});
