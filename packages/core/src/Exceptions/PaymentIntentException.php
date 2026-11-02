<?php

namespace Lunar\Core\Exceptions;

use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Contracts\ReconcilesPaymentIntents;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SyncsPaymentIntents;

/**
 * A payment driver's gateway refused an intent call, could not be reached, or
 * returned something the driver could not interpret, including a reference it
 * does not recognise.
 *
 * Thrown by every method on the pre-order intent capabilities
 * ({@see CreatesPaymentIntents}, {@see SyncsPaymentIntents},
 * {@see ReconcilesPaymentIntents}, {@see SupportsPaymentHolds}). A caller
 * catches this type rather than \Throwable, so it cannot confuse a gateway
 * that went quiet with a bug in its own code, and treats it as "not done,
 * ask again", never as success.
 */
class PaymentIntentException extends LunarException
{
    //
}
