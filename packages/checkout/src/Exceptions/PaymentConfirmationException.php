<?php

namespace Lunar\Checkout\Exceptions;

/**
 * The pay-boundary gate (spec 0010 §E) or completion re-verify (§E.2) found the
 * live cart no longer matches what the customer confirmed. Transports map this
 * to a 409 carrying the re-synced DTO + fresh confirmation token.
 */
class PaymentConfirmationException extends \RuntimeException
{
    public function __construct(
        public readonly string $reason = 'fingerprint_mismatch',
        string $message = '',
    ) {
        parent::__construct($message !== '' ? $message : "Payment confirmation rejected [{$reason}].");
    }
}
