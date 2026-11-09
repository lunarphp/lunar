<?php

namespace Lunar\Core\Enums;

/**
 * Gateway-neutral status of an intent. What each operation does in each status
 * is the lifecycle table in spec 0077.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements the payment intent capabilities.
 */
enum PaymentIntentStatus: string
{
    /** Waiting on the customer, including after a retryable decline. */
    case RequiresAction = 'requires_action';

    /** The customer has acted and the gateway has not decided. */
    case Processing = 'processing';

    /** Authorised and held, waiting for a capture. */
    case RequiresCapture = 'requires_capture';

    /** Money taken. Refunds show in the snapshot's amounts. */
    case Captured = 'captured';

    /** Cancelled before capture; nothing is held. */
    case Voided = 'voided';

    /** Terminal: declined for good or expired. */
    case Failed = 'failed';
}
