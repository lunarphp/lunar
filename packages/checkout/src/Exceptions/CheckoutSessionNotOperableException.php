<?php

namespace Lunar\Checkout\Exceptions;

/**
 * The session is expired or terminal and cannot be operated on (spec 0004 §F —
 * maps to a 410).
 */
class CheckoutSessionNotOperableException extends \RuntimeException {}
