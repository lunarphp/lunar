<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\States\Order\OrderState;

class PaymentFailed extends OrderState
{
    public static string $name = 'payment-failed';

    public function label(): string
    {
        return __('lunar::states.order.payment-failed');
    }
}
