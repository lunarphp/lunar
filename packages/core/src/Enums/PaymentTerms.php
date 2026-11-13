<?php

namespace Lunar\Core\Enums;

/**
 * When an order is meant to be paid. Terms never change the payment status;
 * they explain why an order is still `Pending`.
 */
enum PaymentTerms: string
{
    case Checkout = 'checkout';

    case OnAccount = 'on-account';

    case OnPickup = 'on-pickup';

    public function label(): string
    {
        return __("lunar::base.payment-terms.{$this->value}");
    }
}
