<?php

namespace Lunar\Core\States\Order\Payment;

class Authorized extends PaymentStatus
{
    public static string $name = 'authorized';

    public function label(): string
    {
        return __('lunar::states.payment.authorized');
    }
}
