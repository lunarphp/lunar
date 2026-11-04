<?php

namespace Lunar\Core\Exceptions;

/**
 * Thrown by every payment intent capability method when the gateway refuses
 * or cannot be reached, or the call is not allowed in the intent's status.
 */
class PaymentIntentException extends LunarException
{
    //
}
