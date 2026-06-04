<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\OrderState;

class Returned extends OrderState
{
    public static string $name = 'returned';

    public function label(): string
    {
        return __('lunar::states.order.returned');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Returned;
    }
}
