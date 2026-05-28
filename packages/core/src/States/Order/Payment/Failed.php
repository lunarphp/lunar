<?php

namespace Lunar\Core\States\Order\Payment;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\PaymentState;

class Failed extends PaymentState
{
    public static string $name = 'failed';

    public function label(): string
    {
        return __('lunar::states.payment.failed');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Failed;
    }
}
