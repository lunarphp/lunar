<?php

namespace Lunar\Checkout\Contracts;

use Illuminate\Support\Collection;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Core\Models\Cart;

/**
 * Supplies the places a cart may be collected from (spec 0013 §A). The host
 * binds this in its own provider; the package never binds a default. Unbound
 * or empty means "no pickup points": the order simply collects.
 */
interface PickupPointProvider
{
    /**
     * @return Collection<int, PickupPoint>
     */
    public function pointsFor(Cart $cart): Collection;
}
