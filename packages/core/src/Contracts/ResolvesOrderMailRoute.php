<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Models\Order;

/**
 * Implemented by an order notification that resolves its own mail recipient
 * rather than using one of the order's contacts. Takes precedence over
 * {@see RoutesToOrderContact} and the billing/shipping default; return null (or
 * an empty value) to defer to that default instead.
 *
 * Use this when the recipient isn't an order contact — an account email, an ops
 * inbox, a value off the notification's own payload, etc.
 */
interface ResolvesOrderMailRoute
{
    /**
     * The mail recipient(s) for this notification — an email string, an
     * `[email => name]` map, or null to defer to the order's contact routing.
     *
     * @return string|array<string, string>|null
     */
    public function mailRouteForOrder(Order $order): string|array|null;
}
