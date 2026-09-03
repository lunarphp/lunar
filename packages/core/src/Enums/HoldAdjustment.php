<?php

namespace Lunar\Core\Enums;

use Lunar\Core\Contracts\SupportsPaymentHolds;

/**
 * Outcome of bringing an authorised hold to a new payable total
 * ({@see SupportsPaymentHolds::adjustHold()}).
 */
enum HoldAdjustment: string
{
    /** The hold now covers the new total (it already did, or was incremented). */
    case Ok = 'ok';

    /**
     * This hold cannot stretch to the new total; the customer must
     * re-authorise (re-open the wallet for a fresh hold).
     */
    case NeedsReauthorization = 'needs_reauthorization';
}
