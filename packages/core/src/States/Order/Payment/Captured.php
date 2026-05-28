<?php

namespace Lunar\Core\States\Order\Payment;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\PaymentState;

class Captured extends PaymentState
{
    public static string $name = 'captured';

    public function label(): string
    {
        return __('lunar::states.payment.captured');
    }

    public function category(): StateCategory
    {
        return StateCategory::Complete;
    }
}
