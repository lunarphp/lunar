<?php

namespace Lunar\Core\States\Order\Payment;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\PaymentState;

class Pending extends PaymentState
{
    public static string $name = 'pending';

    public function label(): string
    {
        return __('lunar::states.payment.pending');
    }

    public function category(): StateCategory
    {
        return StateCategory::Pending;
    }
}
