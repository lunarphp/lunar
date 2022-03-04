<?php

namespace GetCandy\PaymentTypes;

use GetCandy\Base\DataTransferObjects\PaymentRefund;
use GetCandy\Base\DataTransferObjects\PaymentRelease;
use GetCandy\Models\Transaction;

class OfflinePayment extends AbstractPayment
{
    /**
     * {@inheritDoc}
     */
    public function release(): PaymentRelease
    {
        if (!$this->order) {
            if (!$this->order = $this->cart->order) {
                $this->order = $this->cart->getManager()->createOrder();
            }
        }

        $this->order->update([
            'status' => $this->config['released'] ?? null,
            'placed_at' => now(),
        ]);

        return new PaymentRelease(true);
    }

    /**
     * {@inheritDoc}
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        return new PaymentRefund(true);
    }
}
