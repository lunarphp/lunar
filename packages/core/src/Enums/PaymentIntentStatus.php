<?php

namespace Lunar\Core\Enums;

use Lunar\Core\Contracts\SupportsPaymentIntents;

/**
 * Gateway-neutral status of a payment intent, as reported by a driver
 * implementing {@see SupportsPaymentIntents}.
 *
 * Deliberately small: these are the outcomes a caller branches on, not a
 * mirror of any one gateway's status list. A driver maps its own statuses
 * onto these, and anything still in flight — awaiting customer action,
 * processing, awaiting a payment method — is Pending.
 */
enum PaymentIntentStatus: string
{
    case Pending = 'pending';
    case RequiresCapture = 'requires_capture';
    case Captured = 'captured';
    case Voided = 'voided';
    case Failed = 'failed';
}
