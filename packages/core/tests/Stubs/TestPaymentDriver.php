<?php

namespace GetCandy\Tests\Stubs;

use GetCandy\Base\DataTransferObjects\PaymentAuthorize;
use GetCandy\Base\DataTransferObjects\PaymentCapture;
use GetCandy\Base\DataTransferObjects\PaymentRefund;
use GetCandy\Models\Transaction;
use GetCandy\PaymentTypes\AbstractPayment;

class TestPaymentDriver extends AbstractPayment
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): PaymentAuthorize
    {
        return new PaymentAuthorize(true);
    }

    /**
     * {@inheritDoc}
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        return new PaymentRefund(true);
    }

    /**
     * {@inheritDoc}
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(true);
    }
}
