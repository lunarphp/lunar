<?php

namespace Lunar\Core\Enums;

use Lunar\Core\Contracts\SupportsPaymentHolds;

/**
 * Outcome of bringing an authorised hold to a new payable total
 * ({@see SupportsPaymentHolds::adjustHold()}).
 */
enum HoldAdjustment: string
{
    /** Coverage is confirmed: the hold already covered the new total, or the gateway confirmed the increment. */
    case Ok = 'ok';

    /**
     * This hold cannot be confirmed to stretch to the new total; the
     * customer must authorise again (re-open the wallet for a fresh hold).
     */
    case NeedsReauthorization = 'needs_reauthorization';
}
