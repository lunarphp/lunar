<?php

namespace Lunar\Core\States\Order\Payment;

class PartiallyRefunded extends PaymentStatus
{
    public static string $name = 'partially-refunded';

    public function label(): string
    {
        return __('lunar::states.payment.partially-refunded');
    }
}
