<?php

namespace Lunar\Core\DataObjects;

/**
 * Gateway-neutral handle to a created intent.
 *
 * `reference` is what the backend reconciles by. `redirectUrl` is set when the
 * customer completes payment on a page the gateway hosts. `clientPayload` is
 * whatever the driver's own frontend component needs to confirm in-page (a
 * client secret, a session id and data); it is opaque to core and to the
 * caller, which passes it to that component unread. A flow whose only input
 * is the reference leaves both empty.
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
