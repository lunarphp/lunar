<?php

namespace Lunar\Core\States\Order\Payment;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\PaymentState;

class Authorized extends PaymentState
{
    public static string $name = 'authorized';

    public function label(): string
    {
        return __('lunar::states.payment.authorized');
    }

    public function category(): StateCategory
    {
        return StateCategory::Active;
    }
}
