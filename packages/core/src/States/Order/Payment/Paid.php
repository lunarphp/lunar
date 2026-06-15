<?php

namespace Lunar\Core\States\Order\Payment;

class Paid extends PaymentStatus
{
    public static string $name = 'paid';

    public function label(): string
    {
        return __('lunar::states.payment.paid');
    }
}
