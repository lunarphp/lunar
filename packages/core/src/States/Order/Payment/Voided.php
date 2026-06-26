<?php

namespace Lunar\Core\States\Order\Payment;

class Voided extends PaymentStatus
{
    public static string $name = 'voided';

    public function label(): string
    {
        return __('lunar::states.payment.voided');
    }
}
