<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Core\Models\Cart;

/**
 * Optional (spec 0011 §H): one sentence for the customer about why delivery
 * looks the way it does for this cart, shown on the shipping step in place
 * of the package's generic "no delivery options" line. Only the host knows
 * why its zones offered nothing ("we do not deliver to Northern Ireland"),
 * so the package never binds a default. Null means nothing to add.
 */
interface DeliveryNotice
{
    public function noticeFor(Cart $cart): ?string;
}
