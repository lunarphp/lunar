<?php

namespace Lunar\Core\States\Order\Order;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\OrderState;

class AwaitingPayment extends OrderState
{
    public static string $name = 'awaiting-payment';

    public function label(): string
    {
        return __('lunar::states.order.awaiting-payment');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Unpaid;
    }
}
