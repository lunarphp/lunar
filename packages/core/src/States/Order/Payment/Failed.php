<?php

namespace Lunar\Core\States\Order\Payment;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\PaymentState;

class Failed extends PaymentState
{
    public static string $name = 'failed';

    public function label(): string
    {
        return __('lunar::states.payment.failed');
    }

    public function category(): StateCategory
    {
        return StateCategory::Failed;
    }
}
