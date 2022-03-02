<?php

namespace GetCandy\PaymentTypes;

class OfflinePayment extends AbstractPayment
{
    public function release()
    {
        // Charge!
    }

    public function refund(int $amount)
    {

    }
}
