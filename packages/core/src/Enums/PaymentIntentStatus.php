<?php

namespace Lunar\Core\Enums;

use Lunar\Core\Contracts\ReconcilesPaymentIntents;

/**
 * Gateway-neutral status of an intent, as reported by a driver implementing
 * {@see ReconcilesPaymentIntents}. These are the outcomes a caller branches
 * on, not a mirror of any one gateway's status list; a driver maps its own
 * statuses onto them. Refund state is not a status: it lives in the
 * snapshot's amounts.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements the payment intent capabilities.
 */
enum PaymentIntentStatus: string
{
    /** Waiting on the customer (no payment method yet, a challenge, an unapproved order). Nothing is moving, so voiding is safe. */
    case RequiresAction = 'requires_action';

    /** The customer has acted and the gateway has not decided. It may still succeed, so it is neither abandoned nor voidable. */
    case Processing = 'processing';

    /** Authorised and held, waiting for a capture. */
    case RequiresCapture = 'requires_capture';

    /** Money taken. Refunds show in the snapshot's amounts. */
    case Captured = 'captured';

    /** Cancelled before capture; nothing is held. */
    case Voided = 'voided';

    /** Declined or expired; nothing is held. */
    case Failed = 'failed';
}
