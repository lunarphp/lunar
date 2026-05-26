<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Orders\CaptureOrder;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;
use Lunar\Tests\Core\Stubs\TestPaymentDriver;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    Payments::extend('testing', fn ($app) => $app->make(TestPaymentDriver::class));
});

it('captures a successful intent within the transaction amount', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    $intent = Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'intent',
        'success' => true,
        'driver' => 'testing',
        'amount' => 10_000,
    ]);

    $result = CaptureOrder::run($order, $intent->id, '50.00');

    expect($result)->toBeInstanceOf(PaymentCapture::class);
    expect($result->success)->toBeTrue();
});

it('rejects capturing more than the intent amount', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    $intent = Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'intent',
        'success' => true,
        'driver' => 'testing',
        'amount' => 5_000,
    ]);

    CaptureOrder::run($order, $intent->id, '75.00');
})->throws(OrderActionException::class, 'Capture amount exceeds the transaction amount.');

it('rejects capturing against a non-intent transaction', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    $capture = Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 5_000,
    ]);

    CaptureOrder::run($order, $capture->id, '10.00');
})->throws(OrderActionException::class);

it('reports canRun false when a capture already exists', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'intent',
        'success' => true,
        'driver' => 'testing',
        'amount' => 5_000,
    ]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 5_000,
    ]);

    expect(CaptureOrder::canRun($order))->toBeFalse();
});
