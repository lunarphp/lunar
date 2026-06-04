<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\OrderState;

class Refunded extends OrderState
{
    public static string $name = 'refunded';

    public function label(): string
    {
        return __('lunar::states.order.refunded');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Cancelled;
    }
}
