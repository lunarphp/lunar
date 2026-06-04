<?php

namespace Lunar\Core\States\Order\Payment;

class Refunded extends PaymentState
{
    public static string $name = 'refunded';

    public function label(): string
    {
        return __('lunar::states.payment.refunded');
    }
}
