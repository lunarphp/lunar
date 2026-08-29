<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Events\Orders\OrderRefunded;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\RefundLine;
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

    $result = app(RefundOrder::class)->execute($order, new RefundRequest(
        transactionId: $capture->id,
        adjustment: '25.00',
    ));

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

    app(RefundOrder::class)->execute($order, new RefundRequest(
        transactionId: $capture->id,
        adjustment: '75.00',
    ));
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

    app(RefundOrder::class)->execute($order, new RefundRequest(
        transactionId: $intent->id,
        adjustment: '10.00',
    ));
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

it('records a line allocation and bumps refunded_quantity', function () {
    Event::fake([OrderRefunded::class]);

    $order = Order::factory()->create(['currency_code' => $this->currency->code]);
    $line = OrderLine::factory()->for($order)->create(['quantity' => 5, 'unit_price' => 1_000, 'total' => 5_000]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 5_000,
    ]);

    app(RefundOrder::class)->execute($order, new RefundRequest(
        transactionId: $order->transactions()->whereType('capture')->firstOrFail()->id,
        lines: [['order_line_id' => $line->id, 'quantity' => 2]],
    ));

    $line->refresh();
    expect($line->refunded_quantity)->toBe(2);

    $refundLine = RefundLine::query()->where('order_line_id', $line->id)->firstOrFail();
    expect($refundLine->quantity)->toBe(2);
    expect($refundLine->amount)->toBe(2_000);

    Event::assertDispatched(OrderRefunded::class, fn (OrderRefunded $event) => $event->order->is($order) && $event->notify === true);
});

it('rejects a line quantity beyond what remains refundable', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);
    $line = OrderLine::factory()->for($order)->create(['quantity' => 2, 'unit_price' => 1_000, 'total' => 2_000, 'refunded_quantity' => 1]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 2_000,
    ]);

    app(RefundOrder::class)->execute($order, new RefundRequest(
        transactionId: $order->transactions()->whereType('capture')->firstOrFail()->id,
        lines: [['order_line_id' => $line->id, 'quantity' => 2]],
    ));
})->throws(OrderActionException::class);

it('rejects a line that does not belong to the order', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);
    $otherOrder = Order::factory()->create(['currency_code' => $this->currency->code]);
    $foreignLine = OrderLine::factory()->for($otherOrder)->create(['quantity' => 1, 'total' => 1_000]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 5_000,
    ]);

    app(RefundOrder::class)->execute($order, new RefundRequest(
        transactionId: $order->transactions()->whereType('capture')->firstOrFail()->id,
        lines: [['order_line_id' => $foreignLine->id, 'quantity' => 1]],
    ));
})->throws(OrderActionException::class);

it('combines lines, shipping, and a manual adjustment into one refund amount', function () {
    $order = Order::factory()->create(['currency_code' => $this->currency->code]);
    $line = OrderLine::factory()->for($order)->create(['quantity' => 2, 'unit_price' => 1_000, 'total' => 2_000]);

    Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'capture',
        'success' => true,
        'driver' => 'testing',
        'amount' => 4_000,
    ]);

    app(RefundOrder::class)->execute($order, new RefundRequest(
        transactionId: $order->transactions()->whereType('capture')->firstOrFail()->id,
        lines: [['order_line_id' => $line->id, 'quantity' => 1]],
        shipping: '5.00',
        adjustment: '5.00',
    ));

    // 1 unit of a £20 line (£10/unit) + £5 shipping + £5 adjustment = £20.
    expect(RefundOrder::availableToRefund($order))->toBe(2_000);
});
