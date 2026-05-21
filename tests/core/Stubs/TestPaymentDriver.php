<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Core\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Core\Base\DataTransferObjects\PaymentCapture;
use Lunar\Core\Base\DataTransferObjects\PaymentRefund;
use Lunar\Core\Models\Contracts\Transaction as TransactionContract;
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
    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        return new PaymentRefund(true);
    }

    /**
     * {@inheritDoc}
     */
    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(true);
    }
}
