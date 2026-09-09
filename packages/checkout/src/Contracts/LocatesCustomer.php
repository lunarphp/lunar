<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Checkout\DataTypes\Coordinates;
use Lunar\Core\Models\Cart;

/**
 * Optional companion to {@see PickupPointProvider} (spec 0013 §A): where the
 * customer is, as far as the host can tell, so the branch list can lead with
 * the nearest. A host typically geocodes the cart's shipping postcode; how is
 * its own concern. Null means "unknown", and the checkout then offers the
 * browser's location instead.
 */
interface LocatesCustomer
{
    public function originFor(Cart $cart): ?Coordinates;
}
