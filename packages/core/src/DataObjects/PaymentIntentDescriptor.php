<?php

namespace Lunar\Core\DataObjects;

/**
 * A created intent. `redirectUrl` is set when the customer pays on a
 * gateway-hosted page; `clientPayload` is what the driver's frontend needs to
 * confirm in-page, passed on unread.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements the payment intent capabilities.
 */
class PaymentIntentDescriptor
{
    /**
     * @param  array<string, mixed>  $clientPayload
     */
    public function __construct(
        public readonly string $reference,
        public readonly ?string $redirectUrl = null,
        public readonly array $clientPayload = [],
    ) {}
}
