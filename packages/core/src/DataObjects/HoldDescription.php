<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Enums\PaymentIntentStatus;

/**
 * Gateway-neutral description of an authorise-only hold, as reported by
 * {@see SupportsPaymentHolds::describeHold()}: a caller guards on `status`,
 * and renders the authorised amount and the wallet it came from ("authorised
 * with Apple Pay") without knowing any gateway's wallet taxonomy.
 *
 * It carries no currency or owner: a driver only describes a hold that
 * belongs to the cart it was asked about and is in that cart's currency.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements the payment intent capabilities.
 */
class HoldDescription
{
    public function __construct(
        public readonly PaymentIntentStatus $status,
        public readonly int $amountMinor,
        public readonly ?string $walletLabel = null,
    ) {}
}
