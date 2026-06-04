<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\OrderState;

class Cancelled extends OrderState
{
    public static string $name = 'cancelled';

    public function label(): string
    {
        return __('lunar::states.order.cancelled');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Cancelled;
    }
}
