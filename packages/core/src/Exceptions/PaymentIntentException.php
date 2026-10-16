<?php

namespace Lunar\Core\Exceptions;

use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SupportsPaymentIntents;

/**
 * A payment driver could not establish what happened to an intent: the gateway
 * was unreachable, refused the call, or does not recognise the reference.
 *
 * Thrown by the pre-order intent capabilities ({@see SupportsPaymentIntents},
 * {@see SupportsPaymentHolds}) wherever an unknown outcome must not be read as
 * a settled one. A caller treats it as "still unresolved, ask again later",
 * never as success or failure.
 */
class PaymentIntentException extends LunarException
{
    //
}
