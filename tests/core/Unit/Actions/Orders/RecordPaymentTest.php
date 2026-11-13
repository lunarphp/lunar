<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Orders\RecordPayment;
use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Core\States\Order\Payment\PartiallyPaid;
use Lunar\Core\States\Order\Payment\PartiallyRefunded;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->currency = Currency::factory()->create(['default' => true, 'decimal_places' => 2]);

    $this->order = Order::factory()->create([
        'currency_code' => $this->currency->code,
        'total' => 10_000,
        'payment_terms' => PaymentTerms::OnAccount,
        'placed_at' => now(),
    ]);
});

it('records a payment as a successful offline capture', function () {
    $transaction = $this->order->recordPayment(10_000, 'INV-1001', 'Bank transfer');

    expect($transaction->type)->toBe('capture')
        ->and($transaction->success)->toBeTrue()
        ->and($transaction->driver)->toBe('offline')
        ->and($transaction->amount)->toBe(10_000)
        ->and($transaction->reference)->toBe('INV-1001')
        ->and($transaction->notes)->toBe('Bank transfer');

    $order = $this->order->refresh();

    expect($order->payment_status)->toBeInstanceOf(Paid::class)
        ->and($order->isAwaitingPaymentByTerms())->toBeFalse()
        ->and(RecordPayment::outstandingBalance($order))->toBe(0)
        ->and(RecordPayment::canRun($order))->toBeFalse();
});

it('records a part payment and leaves the rest outstanding', function () {
    $this->order->recordPayment(4_000);

    $order = $this->order->refresh();

    expect($order->payment_status)->toBeInstanceOf(PartiallyPaid::class)
        ->and(RecordPayment::outstandingBalance($order))->toBe(6_000)
        ->and(RecordPayment::canRun($order))->toBeTrue()
        ->and($order->transactions()->first()->reference)->not->toBeEmpty();
});

it('rejects a payment above the outstanding balance', function () {
    $this->order->recordPayment(4_000);

    $this->order->refresh()->recordPayment(6_001);
})->throws(OrderActionException::class);

it('rejects a payment that is not positive', function () {
    $this->order->recordPayment(0);
})->throws(OrderActionException::class);

it('can not record a payment on a cancelled order', function () {
    $this->order->update(['cancelled_at' => now()]);

    expect(RecordPayment::canRun($this->order))->toBeFalse();
});

it('refunds a recorded payment through the offline driver', function () {
    $transaction = $this->order->recordPayment(10_000);

    $refund = $this->order->refresh()->refund(new RefundRequest(
        transactionId: $transaction->id,
        adjustment: '25.00',
    ));

    expect($refund->success)->toBeTrue()
        ->and($refund->transaction->driver)->toBe('offline')
        ->and($refund->transaction->amount)->toBe(2_500)
        ->and($this->order->refresh()->payment_status)->toBeInstanceOf(PartiallyRefunded::class)
        ->and(RecordPayment::outstandingBalance($this->order))->toBe(0);
});
