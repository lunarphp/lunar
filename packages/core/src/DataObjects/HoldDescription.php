<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Enums\PaymentIntentStatus;

/**
 * Gateway-neutral snapshot of an authorise-only hold, read by the checkout
 * confirm page: the guard checks {@see $status}, the page copy renders the
 * authorised amount and the wallet it came from ("authorised with Apple Pay").
 */
class HoldDescription
{
    public function __construct(
        public readonly PaymentIntentStatus $status,
        public readonly int $amountMinor,
        public readonly ?string $walletLabel = null,
    ) {}
}
