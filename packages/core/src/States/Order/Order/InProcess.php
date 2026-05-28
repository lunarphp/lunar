<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\States\Order\OrderState;

class InProcess extends OrderState
{
    public static string $name = 'in-process';

    public function label(): string
    {
        return __('lunar::states.order.in-process');
    }
}
