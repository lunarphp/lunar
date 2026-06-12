<?php

namespace Lunar\Checkout\Exceptions;

/**
 * A write was refused by the session's current state (spec 0003 §H — maps to a
 * 409). `$reason` is a stable machine code (`sibling_payment_processing`,
 * `stalled`, `frozen`, `superseded_race`), never prose.
 */
class CheckoutSessionConflictException extends \RuntimeException
{
    public function __construct(
        public readonly string $reason,
        string $message = '',
    ) {
        parent::__construct($message !== '' ? $message : "Checkout session conflict [{$reason}].");
    }
}
