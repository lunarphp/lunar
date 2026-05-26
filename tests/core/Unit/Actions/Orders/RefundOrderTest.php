<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\DataObjects\PaymentRefund;
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

it('refunds a successful capture within the available balance', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    $capture = Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 10_000,
    ]);

    $result = app(RefundOrder::class)->execute($order, $capture->id, '25.00');

    expect($result)->toBeInstanceOf(PaymentRefund::class);
    expect($result->success)->toBeTrue();
});

it('rejects refund amounts that exceed available to refund', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    $capture = Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 5_000,
    ]);

    app(RefundOrder::class)->execute($order, $capture->id, '75.00');
})->throws(OrderActionException::class, 'Refund amount exceeds the available amount on this order.');

it('rejects refunding against a non-capture transaction', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    $intent = Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'intent',
        'success' => true,
        'driver' => 'testing',
        'amount' => 5_000,
    ]);

    app(RefundOrder::class)->execute($order, $intent->id, '10.00');
})->throws(OrderActionException::class);

it('subtracts existing refunds from the available balance', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 10_000,
    ]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'refund',
        'success' => true,
        'driver' => 'testing',
        'amount' => 8_000,
    ]);

    expect(RefundOrder::availableToRefund($order))->toBe(2_000);
    expect(RefundOrder::canRun($order))->toBeTrue();
});

it('reports canRun false when fully refunded', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 1_000,
    ]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'refund',
        'success' => true,
        'driver' => 'testing',
        'amount' => 1_000,
    ]);

    expect(RefundOrder::canRun($order))->toBeFalse();
});
