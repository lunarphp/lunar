<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Enums\PaymentIntentStatus;

/**
 * Gateway-neutral snapshot of an authorise-only hold, as reported by a driver
 * implementing {@see SupportsPaymentHolds}: a caller guards on {@see $status},
 * and renders the authorised amount and the wallet it came from ("authorised
 * with Apple Pay") without knowing any gateway's wallet taxonomy.
 */
class HoldDescription
{
    public function __construct(
        public readonly PaymentIntentStatus $status,
        public readonly int $amountMinor,
        public readonly ?string $walletLabel = null,
    ) {}
}
