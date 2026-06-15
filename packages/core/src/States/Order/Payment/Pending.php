<?php

namespace Lunar\Core\States\Order\Payment;

class Pending extends PaymentStatus
{
    public static string $name = 'pending';

    public function label(): string
    {
        return __('lunar::states.payment.pending');
    }
}
