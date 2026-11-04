<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Enums\PaymentIntentStatus;

/**
 * An intent's status and money, in the intent's currency. `refunded` includes
 * pending refunds.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements the payment intent capabilities.
 */
class PaymentIntentSnapshot
{
    public function __construct(
        public readonly PaymentIntentStatus $status,
        public readonly PriceValue $amount,
        public readonly PriceValue $captured,
        public readonly PriceValue $refunded,
        public readonly ?string $walletLabel = null,
    ) {}
}
