<?php

namespace Lunar\Core\Enums;

use Lunar\Core\Contracts\SupportsPaymentIntents;

/**
 * Gateway-neutral status of a payment intent, as reported by a driver
 * implementing {@see SupportsPaymentIntents}.
 */
enum PaymentIntentStatus: string
{
    case Pending = 'pending';
    case RequiresCapture = 'requires_capture';
    case Captured = 'captured';
    case Voided = 'voided';
    case Failed = 'failed';
}
