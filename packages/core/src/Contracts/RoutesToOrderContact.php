<?php

namespace Lunar\Core\Contracts;

/**
 * Implemented by an order notification that should be delivered to a specific
 * order contact rather than the default. Order-level notifications (payment,
 * cancellation, the fulfilment rollup) go to the billing contact by default;
 * per-fulfilment notifications ("your parcel has shipped") implement this to target
 * the shipping contact instead. The order's `routeNotificationForMail()` reads
 * it and falls back to the other contact when the chosen one has no email.
 */
interface RoutesToOrderContact
{
    /**
     * The order address type whose contact email should receive this
     * notification.
     *
     * @return 'billing'|'shipping'
     */
    public function orderContactType(): string;
}
