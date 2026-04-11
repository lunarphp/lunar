<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Lunar\Actions\Orders\RefundTransaction;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Base\DataTransferObjects\RefundAuthorizationResult;
use Lunar\Base\DataTransferObjects\RefundRequest;
use Lunar\Base\RefundAuthorizationInterface;
use Lunar\Events\RefundCompleted;
use Lunar\Events\RefundFailed;
use Lunar\Events\RefundRequested;
use Lunar\Facades\Payments;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    Currency::factory()->create([
        'default' => true,
        'code' => 'USD',
        'decimal_places' => 2,
        'exchange_rate' => 1,
    ]);

    Config::set('lunar.payments.types.testing-refund', [
        'driver' => 'testing-refund',
        'authorized' => 'paid',
    ]);

    Payments::extend('testing-refund', fn () => new class extends AbstractPayment
    {
        public function authorize(): ?PaymentAuthorize
        {
            return new PaymentAuthorize(success: true);
        }

        public function refund(Lunar\Models\Contracts\Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
        {
            if (app()->bound('refund-sequence')) {
                app('refund-sequence')->push('driver');
            }

            if ($notes === 'throw-exception') {
                throw new RuntimeException('Gateway timeout');
            }

            if ($notes === 'driver-fail') {
                return new PaymentRefund(
                    success: false,
                    message: 'Driver refund failed',
                );
            }

            $refundTransaction = $transaction->order->transactions()->create([
                'parent_transaction_id' => $transaction->id,
                'success' => true,
                'type' => 'refund',
                'driver' => 'testing-refund',
                'amount' => $amount,
                'reference' => 'refund-'.$amount,
                'status' => 'refunded',
                'notes' => $notes,
                'card_type' => $transaction->card_type,
                'last_four' => $transaction->last_four,
            ]);

            if ($notes === 'hydrate-me') {
                return new PaymentRefund(success: true);
            }

            return new PaymentRefund(
                success: true,
                refundTransactionId: $refundTransaction->id,
                reference: $refundTransaction->reference,
                status: $refundTransaction->status,
            );
        }

        public function capture(Lunar\Models\Contracts\Transaction $transaction, $amount = 0): PaymentCapture
        {
            return new PaymentCapture(success: true);
        }
    });
});

function makeCaptureTransaction(string $driver = 'testing-refund'): Transaction
{
    $currency = Currency::getDefault();

    $order = Order::factory()->create([
        'currency_code' => $currency->code,
        'placed_at' => now(),
        'total' => 1500,
    ]);

    return Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => $driver,
        'type' => 'capture',
        'success' => true,
        'amount' => 1500,
        'reference' => 'capture-1500',
        'status' => 'captured',
        'card_type' => 'visa',
        'last_four' => '4242',
    ]);
}

it('dispatches refund requested and completed events for an allowed refund', function () {
    $transaction = makeCaptureTransaction();
    $lineAllocations = [
        [
            'order_line_id' => 10,
            'quantity' => 1,
            'amount' => 500,
        ],
    ];

    Event::fake([
        RefundRequested::class,
        RefundCompleted::class,
        RefundFailed::class,
    ]);

    $response = app(RefundTransaction::class)->execute(
        new RefundRequest(
            transaction: $transaction,
            amount: 500,
            notes: 'hydrate-me',
            actorId: 99,
            lineAllocations: $lineAllocations,
        )
    );

    expect($response->success)->toBeTrue()
        ->and($response->refundTransactionId)->not->toBeNull()
        ->and($response->reference)->toBe('refund-500')
        ->and($response->status)->toBe('refunded')
        ->and($response->lineAllocations)->toBe($lineAllocations);

    Event::assertDispatched(RefundRequested::class, function (RefundRequested $event) use ($transaction, $lineAllocations) {
        return $event->refundRequest->transaction->is($transaction)
            && $event->refundRequest->lineAllocations === $lineAllocations;
    });

    Event::assertDispatched(RefundCompleted::class, function (RefundCompleted $event) use ($response, $lineAllocations) {
        return $event->paymentRefund->refundTransactionId === $response->refundTransactionId
            && $event->paymentRefund->lineAllocations === $lineAllocations;
    });

    Event::assertNotDispatched(RefundFailed::class);
});

it('dispatches refund failed when authorization denies the refund', function () {
    $transaction = makeCaptureTransaction();

    app()->bind(RefundAuthorizationInterface::class, fn () => new class implements RefundAuthorizationInterface
    {
        public function authorize(RefundRequest $refundRequest): RefundAuthorizationResult
        {
            return new RefundAuthorizationResult(
                authorized: false,
                message: 'An approved RMA is required',
                meta: ['reason' => 'missing-rma'],
            );
        }
    });

    Event::fake([
        RefundRequested::class,
        RefundCompleted::class,
        RefundFailed::class,
    ]);

    $response = app(RefundTransaction::class)->execute(
        new RefundRequest(
            transaction: $transaction,
            amount: 500,
        )
    );

    expect($response->success)->toBeFalse()
        ->and($response->message)->toBe('An approved RMA is required');

    Event::assertNotDispatched(RefundRequested::class);
    Event::assertNotDispatched(RefundCompleted::class);
    Event::assertDispatched(RefundFailed::class, function (RefundFailed $event) use ($transaction) {
        return $event->refundRequest->transaction->is($transaction)
            && $event->message === 'An approved RMA is required'
            && $event->meta === ['reason' => 'missing-rma'];
    });

    expect($transaction->order->refresh()->refunds)->toHaveCount(0);
});

it('dispatches refund failed when the driver rejects the refund', function () {
    $transaction = makeCaptureTransaction();

    Event::fake([
        RefundRequested::class,
        RefundCompleted::class,
        RefundFailed::class,
    ]);

    $response = app(RefundTransaction::class)->execute(
        new RefundRequest(
            transaction: $transaction,
            amount: 500,
            notes: 'driver-fail',
        )
    );

    expect($response->success)->toBeFalse()
        ->and($response->message)->toBe('Driver refund failed');

    Event::assertDispatched(RefundRequested::class);
    Event::assertNotDispatched(RefundCompleted::class);
    Event::assertDispatched(RefundFailed::class, function (RefundFailed $event) {
        return $event->message === 'Driver refund failed';
    });
});

it('dispatches refund requested before calling the payment driver', function () {
    $transaction = makeCaptureTransaction();

    app()->instance('refund-sequence', collect());

    Event::listen(RefundRequested::class, function () {
        app('refund-sequence')->push('requested');
    });

    app(RefundTransaction::class)->execute(
        new RefundRequest(
            transaction: $transaction,
            amount: 500,
        )
    );

    expect(app('refund-sequence')->all())->toBe([
        'requested',
        'driver',
    ]);
});

it('dispatches refund failed when the driver throws an exception', function () {
    $transaction = makeCaptureTransaction();
    $lineAllocations = [
        [
            'order_line_id' => 11,
            'quantity' => 1,
            'amount' => 500,
        ],
    ];

    Event::fake([
        RefundRequested::class,
        RefundCompleted::class,
        RefundFailed::class,
    ]);

    $response = app(RefundTransaction::class)->execute(
        new RefundRequest(
            transaction: $transaction,
            amount: 500,
            notes: 'throw-exception',
            lineAllocations: $lineAllocations,
        )
    );

    expect($response->success)->toBeFalse()
        ->and($response->message)->toBe('Gateway timeout')
        ->and($response->lineAllocations)->toBe($lineAllocations);

    Event::assertDispatched(RefundRequested::class);
    Event::assertNotDispatched(RefundCompleted::class);
    Event::assertDispatched(RefundFailed::class, function (RefundFailed $event) use ($lineAllocations) {
        return $event->message === 'Gateway timeout'
            && $event->paymentRefund?->lineAllocations === $lineAllocations;
    });

    expect($transaction->order->refresh()->refunds)->toHaveCount(0);
});
