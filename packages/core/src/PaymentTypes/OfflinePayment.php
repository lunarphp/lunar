<?php

namespace GetCandy\PaymentTypes;

use GetCandy\Base\DataTransferObjects\PaymentRelease;

class OfflinePayment extends AbstractPayment
{
    /**
     * {@inheritDoc}
     */
    public function release()
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

        return new PaymentRelease(success: true);
    }

    public function refund(int $amount)
    {
        //
    }
}
