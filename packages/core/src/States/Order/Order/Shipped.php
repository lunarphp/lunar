<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\States\Order\OrderState;

class Shipped extends OrderState
{
    public static string $name = 'shipped';

    public function label(): string
    {
        return __('lunar::states.order.shipped');
    }
}
