<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\States\Order\OrderState;

class PartiallyShipped extends OrderState
{
    public static string $name = 'partially-shipped';

    public function label(): string
    {
        return __('lunar::states.order.partially-shipped');
    }
}
