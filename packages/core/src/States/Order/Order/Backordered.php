<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\States\Order\OrderState;

class Backordered extends OrderState
{
    public static string $name = 'backordered';

    public function label(): string
    {
        return __('lunar::states.order.backordered');
    }
}
