<?php

namespace Lunar\Core\Enums;

use Lunar\Core\Contracts\SupportsPaymentHolds;

/**
 * Outcome of bringing an authorised hold to a new amount
 * ({@see SupportsPaymentHolds::adjustHold()}).
 */
enum HoldAdjustment: string
{
    /** Coverage is confirmed. */
    case Ok = 'ok';

    /** The customer must authorise again: void the hold, then create another. */
    case NeedsReauthorization = 'needs_reauthorization';
}
