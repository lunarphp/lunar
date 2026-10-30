<?php

namespace Lunar\Core\DataObjects;

/**
 * Gateway-neutral handle to a created payment intent: the reference the
 * backend reconciles by, and the client secret the gateway's frontend
 * component confirms with (null for gateways without a client-side step).
 */
class PaymentIntentDescriptor
{
    public function __construct(
        public readonly string $reference,
        public readonly ?string $clientSecret = null,
    ) {}
}
