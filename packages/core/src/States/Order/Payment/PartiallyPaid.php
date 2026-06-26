<?php

namespace Lunar\Core\States\Order\Payment;

class PartiallyPaid extends PaymentStatus
{
    public static string $name = 'partially-paid';

    public function label(): string
    {
        return __('lunar::states.payment.partially-paid');
    }
}
