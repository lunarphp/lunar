<?php

namespace Lunar\Tests\Core\Stubs;

use Illuminate\Support\Str;
use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Models\Transaction;
use Lunar\Core\PaymentTypes\AbstractPayment;

class TestPaymentDriver extends AbstractPayment
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): ?PaymentAuthorize
    {
        return new PaymentAuthorize(true);
    }

    /**
     * {@inheritDoc}
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        $refundTransaction = $transaction->order->transactions()->create([
            'success' => true,
            'type' => 'refund',
            'driver' => 'testing',
            'amount' => $amount,
            'reference' => 'test-refund-'.Str::random(8),
            'status' => 'success',
            'notes' => $notes,
        ]);

        return new PaymentRefund(success: true, transaction: $refundTransaction);
    }

    /**
     * {@inheritDoc}
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(true);
    }
}
