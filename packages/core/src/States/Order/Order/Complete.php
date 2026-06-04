<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\OrderState;

class Complete extends OrderState
{
    public static string $name = 'complete';

    public function label(): string
    {
        return __('lunar::states.order.complete');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Completed;
    }
}
