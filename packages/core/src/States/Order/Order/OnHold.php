<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\States\Order\OrderState;

class OnHold extends OrderState
{
    public static string $name = 'on-hold';

    public function label(): string
    {
        return __('lunar::states.order.on-hold');
    }
}
