<?php

namespace Lunar\Core\States\Order\Payment;

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\States\Order\PaymentState;

class Refunded extends PaymentState
{
    public static string $name = 'refunded';

    public function label(): string
    {
        return __('lunar::states.payment.refunded');
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Complete;
    }
}
