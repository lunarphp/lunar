<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\OrderState;

class InProcess extends OrderState
{
    public static string $name = 'in-process';

    public function label(): string
    {
        return __('lunar::states.order.in-process');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Processing;
    }
}
