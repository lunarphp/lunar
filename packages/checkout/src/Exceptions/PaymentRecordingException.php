<?php

namespace Lunar\Checkout\Exceptions;

use RuntimeException;

/**
 * The gateway could not record a captured payment against the order at
 * completion (spec 0002 §E). The order is still placed: the money exists,
 * and an order without its Transaction rows is recoverable by an operator,
 * whereas a captured charge without an order is not.
 */
class PaymentRecordingException extends RuntimeException {}
